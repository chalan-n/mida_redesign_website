//+------------------------------------------------------------------+
//|                                              WebControl_MT4.mq4 |
//|                                     Copyright 2026, Gemini User |
//|                                          Database: ea_control_db |
//+------------------------------------------------------------------+
//  v7.0 - Fixed conflict with CopyTrade_Master (OrderSelect collision)
//       - Changed from OnTick to OnTimer for reliable timing
//       - Minimized WebRequest blocking impact
//       - Snapshot order data before WebRequest call
//+------------------------------------------------------------------+
#property copyright "CHAL NT"
#property link      ""
#property version   "7.00"
#property strict

// --- Windows DLL Import สำหรับควบคุม AutoTrading ---
#import "user32.dll"
   int PostMessageW(int hWnd, int Msg, int wParam, int lParam);
   int GetAncestor(int hWnd, int gaFlags);
#import

#define WM_KEYDOWN  0x0100
#define WM_KEYUP    0x0102
#define VK_CONTROL  0x11

// --- INPUTS ---
input string InpApiUrl     = "https://chalantorn.club/ea_control/ea_control_api/sync.php"; 
input int    InpTimer       = 3;        // Sync interval (seconds) - min 2
input bool   InpAutoToggle  = true;     // Auto Toggle AutoTrading (ต้องเปิด Allow DLL)
input int    InpTimeout     = 5000;     // WebRequest timeout (ms)
input int    InpMaxRetry    = 3;        // Max retry on close failure

// Global Variable Name สำหรับแชร์สถานะกับ EA อื่น
#define GV_TRADING_ENABLED "WebControl_TradingEnabled_"

// ค่า Global
bool g_trading_enabled = true;
bool g_last_trading_state = true;
bool g_use_http_fallback = false;
int  g_sync_fail_count = 0;

// --- Order Snapshot ---
// เก็บข้อมูล order ไว้ก่อนเรียก WebRequest เพื่อไม่ให้ OrderSelect ชนกับ EA อื่น
struct OrderSnapshot {
   int    ticket;
   string symbol;
   int    type;
   double lots;
   double openPrice;
   datetime openTime;
   double sl;
   double tp;
   double swap;
   double profit;
   int    digits;
};

OrderSnapshot g_orders[];
int    g_orderCount = 0;
long   g_accNumber = 0;
string g_accName = "";
string g_accBroker = "";
double g_balance = 0;
double g_equity = 0;
double g_margin = 0;
double g_freeMargin = 0;
double g_totalProfit = 0;
double g_dailyProfit = 0;

// --- Forward Declarations ---
void   RunSync();
void   SnapshotAccountData();
string BuildJsonFromSnapshot();
double CalcDailyProfit();
void   SyncWithServer(string jsonBody);
void   ProcessServerResponse(string jsonResponse);
void   CloseAllPositions();
void   UpdateChartDisplay();
void   ToggleAutoTrading();
string JsonEscape(string text);

//+------------------------------------------------------------------+
//| Expert initialization function                                   |
//+------------------------------------------------------------------+
int OnInit() {
   if(!MQLInfoInteger(MQL_TESTER)) {
      Print("--- WebControl EA v7.0 (MT4) ---");
      Print("API URL: ", InpApiUrl);
      Print("Sync Interval: ", InpTimer, "s | AutoToggle: ", InpAutoToggle ? "ON" : "OFF");
   }
   
   if(InpAutoToggle && !MQLInfoInteger(MQL_DLLS_ALLOWED)) {
      Print("!!! WARNING: Enable 'Allow DLL imports' for AutoTrading control !!!");
   }
   
   // ใช้ OnTimer แทน OnTick เพื่อ:
   // 1. ทำงานได้แม้ไม่มี tick (ตลาดปิด/symbol นิ่ง)
   // 2. ควบคุม interval ได้แม่นยำกว่า
   int timerSec = MathMax(InpTimer, 2); // ขั้นต่ำ 2 วินาที ป้องกัน WebRequest ซ้อน
   EventSetTimer(timerSec);
   
   // Sync ครั้งแรก
   RunSync();
   return(INIT_SUCCEEDED);
}

void OnDeinit(const int reason) {
   EventKillTimer();
   long acc = AccountInfoInteger(ACCOUNT_LOGIN);
   GlobalVariableDel(GV_TRADING_ENABLED + IntegerToString(acc));
   Comment("");
}

//+------------------------------------------------------------------+
//| OnTimer: sync ตาม interval ที่กำหนด                              |
//| ใช้แทน OnTick เพื่อไม่ขึ้นกับ tick ของ symbol                    |
//+------------------------------------------------------------------+
void OnTimer() {
   RunSync();
}

//+------------------------------------------------------------------+
//| OnTick: ไม่ทำ sync แล้ว ใช้ OnTimer แทน                          |
//| เหลือไว้เผื่อ override ในอนาคต                                    |
//+------------------------------------------------------------------+
void OnTick() {
   // ไม่ทำอะไร - ใช้ OnTimer แทน
}

//+------------------------------------------------------------------+
//| OnChartEvent: คลิกชาร์ตสั่ง sync ทันที                            |
//+------------------------------------------------------------------+
void OnChartEvent(const int id, const long &lparam, const double &dparam, const string &sparam) {
   if(id == CHARTEVENT_CLICK) {
      RunSync();
   }
}

//+------------------------------------------------------------------+
//| Toggle AutoTrading (Ctrl+E)                                      |
//+------------------------------------------------------------------+
void ToggleAutoTrading() {
   if(!MQLInfoInteger(MQL_DLLS_ALLOWED)) {
      Print("Cannot toggle AutoTrading: DLL imports not allowed!");
      return;
   }
   
   int hWnd = GetAncestor(WindowHandle(_Symbol, _Period), 2);
   if(hWnd == 0) {
      Print("Cannot find terminal window!");
      return;
   }
   
   PostMessageW(hWnd, WM_KEYDOWN, VK_CONTROL, 0);
   Sleep(50);
   PostMessageW(hWnd, WM_KEYDOWN, 'E', 0);
   Sleep(50);
   PostMessageW(hWnd, WM_KEYUP, 'E', 0);
   Sleep(50);
   PostMessageW(hWnd, WM_KEYUP, VK_CONTROL, 0);
   
   Print("AutoTrading toggled via Ctrl+E");
}

//+------------------------------------------------------------------+
//| ฟังก์ชันหลัก                                                      |
//| CRITICAL FIX: Snapshot ข้อมูลทั้งหมดก่อน แล้วค่อย WebRequest     |
//| เพื่อไม่ให้ OrderSelect ค้างระหว่างที่ WebRequest block            |
//+------------------------------------------------------------------+
void RunSync() {
   if(AccountInfoInteger(ACCOUNT_LOGIN) == 0) return;
   
   // ===== PHASE 1: Snapshot ข้อมูลทั้งหมดอย่างรวดเร็ว =====
   // ใช้เวลาไม่กี่ ms - ไม่ block EA อื่น
   SnapshotAccountData();
   
   // ===== PHASE 2: สร้าง JSON จาก snapshot (ไม่แตะ OrderSelect) =====
   string jsonPayload = BuildJsonFromSnapshot();
   
   // ===== PHASE 3: ส่ง WebRequest (อาจ block นานถึง InpTimeout ms) =====
   // ตอนนี้ OrderSelect ว่างแล้ว EA อื่นใช้ได้เลย
   SyncWithServer(jsonPayload);
   
   // ===== PHASE 4: อัพเดท chart =====
   UpdateChartDisplay();
}

//+------------------------------------------------------------------+
//| Snapshot ข้อมูล account + orders ทั้งหมดอย่างรวดเร็ว             |
//| ใช้ OrderSelect ให้เสร็จภายในครั้งเดียว ไม่ทิ้งค้าง               |
//+------------------------------------------------------------------+
void SnapshotAccountData() {
   // Account info - ไม่ใช้ OrderSelect
   g_accNumber = AccountInfoInteger(ACCOUNT_LOGIN);
   g_accName = AccountInfoString(ACCOUNT_NAME);
   g_accBroker = AccountInfoString(ACCOUNT_COMPANY);
   g_balance = AccountInfoDouble(ACCOUNT_BALANCE);
   g_equity = AccountInfoDouble(ACCOUNT_EQUITY);
   g_margin = AccountInfoDouble(ACCOUNT_MARGIN);
   g_freeMargin = AccountInfoDouble(ACCOUNT_MARGIN_FREE);
   g_totalProfit = AccountInfoDouble(ACCOUNT_PROFIT);
   
   // === Snapshot orders: ใช้ OrderSelect ให้เสร็จใน loop เดียว ===
   int total = OrdersTotal();
   ArrayResize(g_orders, total); // จอง array ล่วงหน้า
   g_orderCount = 0;
   
   for(int i = 0; i < total; i++) {
      if(!OrderSelect(i, SELECT_BY_POS, MODE_TRADES)) continue;
      
      int type = OrderType();
      if(type != OP_BUY && type != OP_SELL) continue;
      
      g_orders[g_orderCount].ticket    = OrderTicket();
      g_orders[g_orderCount].symbol    = OrderSymbol();
      g_orders[g_orderCount].type      = type;
      g_orders[g_orderCount].lots      = OrderLots();
      g_orders[g_orderCount].openPrice = OrderOpenPrice();
      g_orders[g_orderCount].openTime  = OrderOpenTime();
      g_orders[g_orderCount].sl        = OrderStopLoss();
      g_orders[g_orderCount].tp        = OrderTakeProfit();
      g_orders[g_orderCount].swap      = OrderSwap();
      g_orders[g_orderCount].profit    = OrderProfit();
      g_orders[g_orderCount].digits    = (int)SymbolInfoInteger(OrderSymbol(), SYMBOL_DIGITS);
      g_orderCount++;
   }
   ArrayResize(g_orders, g_orderCount);
   
   // === Daily profit: ใช้ OrderSelect อีก loop แยก (history) ===
   g_dailyProfit = CalcDailyProfit();
}

//+------------------------------------------------------------------+
//| คำนวณกำไรวันนี้                                                   |
//+------------------------------------------------------------------+
double CalcDailyProfit() {
   double profit = 0.0;
   datetime startOfDay = iTime(NULL, PERIOD_D1, 0);
   if(startOfDay == 0) return 0.0;
   
   // Closed orders profit
   int total = OrdersHistoryTotal();
   for(int i = 0; i < total; i++) {
      if(OrderSelect(i, SELECT_BY_POS, MODE_HISTORY)) {
         if(OrderCloseTime() >= startOfDay) {
            profit += OrderProfit() + OrderSwap() + OrderCommission();
         }
      }
   }
   
   // Floating profit จาก snapshot (ไม่ต้อง OrderSelect อีก)
   for(int i = 0; i < g_orderCount; i++) {
      if(g_orders[i].openTime >= startOfDay) {
         profit += g_orders[i].profit + g_orders[i].swap;
      }
   }
   return profit;
}

//+------------------------------------------------------------------+
//| Escape string สำหรับ JSON                                        |
//+------------------------------------------------------------------+
string JsonEscape(string text) {
   StringReplace(text, "\\", "\\\\");
   StringReplace(text, "\"", "\\\"");
   StringReplace(text, "\n", "\\n");
   StringReplace(text, "\r", "\\r");
   StringReplace(text, "\t", "\\t");
   return text;
}

//+------------------------------------------------------------------+
//| สร้าง JSON จาก snapshot (ไม่ใช้ OrderSelect เลย)                 |
//+------------------------------------------------------------------+
string BuildJsonFromSnapshot() {
   string name = JsonEscape(g_accName);
   string broker = JsonEscape(g_accBroker);

   string json = "{";
   json += "\"account_number\": " + IntegerToString(g_accNumber) + ",";
   json += "\"account_name\": \"" + name + "\",";
   json += "\"broker\": \"" + broker + "\",";
   json += "\"platform\": \"MT4\",";
   json += "\"balance\": " + DoubleToString(g_balance, 2) + ",";
   json += "\"equity\": " + DoubleToString(g_equity, 2) + ",";
   json += "\"margin\": " + DoubleToString(g_margin, 2) + ",";
   json += "\"free_margin\": " + DoubleToString(g_freeMargin, 2) + ",";
   json += "\"profit\": " + DoubleToString(g_totalProfit, 2) + ",";
   json += "\"daily_profit\": " + DoubleToString(g_dailyProfit, 2) + ",";
   json += "\"orders\": [";
   
   for(int i = 0; i < g_orderCount; i++) {
      if(i > 0) json += ",";
      string sym = JsonEscape(g_orders[i].symbol);
      int d = g_orders[i].digits;
      json += "{";
      json += "\"ticket\":" + IntegerToString(g_orders[i].ticket) + ",";
      json += "\"symbol\":\"" + sym + "\",";
      json += "\"type\":\"" + (g_orders[i].type == OP_BUY ? "BUY" : "SELL") + "\",";
      json += "\"lots\":" + DoubleToString(g_orders[i].lots, 2) + ",";
      json += "\"open_price\":" + DoubleToString(g_orders[i].openPrice, d) + ",";
      json += "\"open_time\":\"" + TimeToString(g_orders[i].openTime, TIME_DATE|TIME_SECONDS) + "\",";
      json += "\"sl\":" + DoubleToString(g_orders[i].sl, d) + ",";
      json += "\"tp\":" + DoubleToString(g_orders[i].tp, d) + ",";
      json += "\"swap\":" + DoubleToString(g_orders[i].swap, 2) + ",";
      json += "\"profit\":" + DoubleToString(g_orders[i].profit, 2);
      json += "}";
   }
   
   json += "]}";
   return json;
}

//+------------------------------------------------------------------+
//| ส่ง WebRequest (HTTPS -> HTTP fallback)                          |
//| ณ จุดนี้ไม่มี OrderSelect ค้าง - ปลอดภัยที่จะ block               |
//+------------------------------------------------------------------+
void SyncWithServer(string jsonBody) {
   char postData[];
   char resultData[];
   string resultHeaders;
   
   string headers = "Content-Type: application/json\r\n";
   
   StringToCharArray(jsonBody, postData, 0, WHOLE_ARRAY);
   int sz = ArraySize(postData);
   if (sz > 0 && postData[sz-1] == 0) {
      ArrayResize(postData, sz - 1);
   }

   string url = InpApiUrl;
   if(g_use_http_fallback && StringFind(url, "https://") == 0) {
      url = "http://" + StringSubstr(url, 8);
   }
   
   ResetLastError();
   int res = WebRequest("POST", url, headers, InpTimeout, postData, resultData, resultHeaders);
   
   if (res == -1) {
      int err = GetLastError();
      
      // SSL fallback
      if ((err == 5203) && !g_use_http_fallback && StringFind(InpApiUrl, "https://") == 0) {
         string httpUrl = "http://" + StringSubstr(InpApiUrl, 8);
         
         ArrayFree(postData);
         ArrayFree(resultData);
         StringToCharArray(jsonBody, postData, 0, WHOLE_ARRAY);
         sz = ArraySize(postData);
         if (sz > 0 && postData[sz-1] == 0)
            ArrayResize(postData, sz - 1);
         resultHeaders = "";
         
         ResetLastError();
         res = WebRequest("POST", httpUrl, headers, InpTimeout, postData, resultData, resultHeaders);
         
         if (res >= 0) {
            g_use_http_fallback = true;
            Print("SSL fallback: using HTTP");
         }
         else {
            int err2 = GetLastError();
            if (err2 == 4060) Print("Error: Add http:// URL to Tools > Options > Expert Advisors");
            else Print("WebRequest Error: ", err2);
            g_sync_fail_count++;
            return;
         }
      }
      else if (err == 4060) {
         if(g_sync_fail_count == 0) Print("Error 4060: Add URL to Tools > Options > Expert Advisors");
         g_sync_fail_count++;
         return;
      }
      else {
         if(g_sync_fail_count < 3) Print("WebRequest Error: ", err);
         g_sync_fail_count++;
         return;
      }
   }
   
   if (res != 200) {
      if(g_sync_fail_count < 3) Print("Server Error HTTP ", res);
      g_sync_fail_count++;
   } else {
      if(g_sync_fail_count > 0) {
         Print("Sync recovered after ", g_sync_fail_count, " failures");
      }
      g_sync_fail_count = 0;
      string response = CharArrayToString(resultData);
      if(StringLen(response) > 0) {
         ProcessServerResponse(response);
      }
   }
}

//+------------------------------------------------------------------+
//| ประมวลผลคำตอบจาก Server                                         |
//+------------------------------------------------------------------+
void ProcessServerResponse(string jsonResponse) {
   // เช็คคำสั่ง CLOSE_ALL
   if(StringFind(jsonResponse, "\"type\":\"CLOSE_ALL\"") >= 0) {
      Print("!!! COMMAND: CLOSE ALL !!!");
      CloseAllPositions();
   }
   
   // อ่านค่า trading_enabled
   int pos = StringFind(jsonResponse, "\"trading_enabled\":");
   if(pos >= 0) {
      string sub = StringSubstr(jsonResponse, pos + 18, 5);
      StringTrimLeft(sub);
      StringTrimRight(sub);
      g_trading_enabled = (StringFind(sub, "1") == 0 || StringFind(sub, "t") == 0);
      
      long acc = AccountInfoInteger(ACCOUNT_LOGIN);
      GlobalVariableSet(GV_TRADING_ENABLED + IntegerToString(acc), g_trading_enabled ? 1.0 : 0.0);
      
      if(InpAutoToggle && MQLInfoInteger(MQL_DLLS_ALLOWED)) {
         bool currentAutoTrading = (bool)TerminalInfoInteger(TERMINAL_TRADE_ALLOWED);
         
         if(g_trading_enabled != g_last_trading_state) {
            if(g_trading_enabled && !currentAutoTrading) {
               Print(">>> Enabling AutoTrading... <<<");
               ToggleAutoTrading();
            }
            else if(!g_trading_enabled && currentAutoTrading) {
               Print(">>> Disabling AutoTrading... <<<");
               ToggleAutoTrading();
            }
            g_last_trading_state = g_trading_enabled;
         }
      }
   }
}

//+------------------------------------------------------------------+
//| แสดงสถานะบน Chart                                                |
//+------------------------------------------------------------------+
void UpdateChartDisplay() {
   bool autoTradingOn = (bool)TerminalInfoInteger(TERMINAL_TRADE_ALLOWED);
   string status = g_trading_enabled ? "TRADING: ON" : "TRADING: OFF (PAUSED)";
   string autoStatus = autoTradingOn ? "AutoTrading: ON" : "AutoTrading: OFF";
   string syncStatus = (g_sync_fail_count > 0) 
      ? "SYNC: FAIL(" + IntegerToString(g_sync_fail_count) + ")" 
      : "SYNC: OK";
   string proto = g_use_http_fallback ? "HTTP" : "HTTPS";
   
   Comment("WebControl v7.0 | ", proto, "\n",
           "Account: ", g_accNumber, "\n",
           status, " | ", autoStatus, "\n",
           syncStatus, " | Orders: ", g_orderCount, "\n",
           "Last: ", TimeToString(TimeCurrent(), TIME_SECONDS));
}

//+------------------------------------------------------------------+
//| ฟังก์ชันสำหรับ EA อื่นเรียกใช้                                    |
//+------------------------------------------------------------------+
bool IsTradingAllowed() {
   long acc = AccountInfoInteger(ACCOUNT_LOGIN);
   string gvName = GV_TRADING_ENABLED + IntegerToString(acc);
   
   if(GlobalVariableCheck(gvName)) {
      return GlobalVariableGet(gvName) == 1.0;
   }
   return true;
}

//+------------------------------------------------------------------+
//| ปิดออเดอร์ทั้งหมด                                                 |
//+------------------------------------------------------------------+
void CloseAllPositions() {
   int totalClosed = 0;
   int totalFailed = 0;
   
   for(int i = OrdersTotal() - 1; i >= 0; i--) {
      if(!OrderSelect(i, SELECT_BY_POS, MODE_TRADES)) continue;
      
      int type = OrderType();
      if(type != OP_BUY && type != OP_SELL) continue;
      
      string sym = OrderSymbol();
      int ticket = OrderTicket();
      double lots = OrderLots();
      int slip = 20; 
      long digits = SymbolInfoInteger(sym, SYMBOL_DIGITS);
      if(digits == 3 || digits == 5) slip = 200; 
      
      bool closed = false;
      for(int k = 0; k < InpMaxRetry; k++) {
          RefreshRates();
          double closePrice = (type == OP_BUY) 
             ? MarketInfo(sym, MODE_BID) 
             : MarketInfo(sym, MODE_ASK);
          
          if(closePrice == 0) { Sleep(200); continue; }
          
          if(OrderClose(ticket, lots, closePrice, slip, clrRed)) {
             closed = true;
             totalClosed++;
             break;
          }
          Print("Close retry ", k+1, "/", InpMaxRetry, " #", ticket, " err=", GetLastError());
          Sleep(200);
      }
      if(!closed) {
         totalFailed++;
         Print("!!! FAILED to close #", ticket);
      }
   }
   Print("CloseAll: closed=", totalClosed, " failed=", totalFailed);
}
