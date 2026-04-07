/**
 * Bluebook SPA - Modern JavaScript Application
 * No page reloads, smooth transitions
 */

const BluebookApp = {
    // Current state
    state: {
        currentView: 'home',
        cid: null,
        bid: null,
        mid: null,
        yy: null,
        carid: null,
        version: '',
        webname: ''
    },

    // API Base URL
    apiBase: 'api/getData.php',

    /**
     * Initialize the app
     */
    async init() {
        // Get version info
        await this.getVersion();

        // Setup history handler
        window.addEventListener('popstate', (e) => {
            if (e.state) {
                this.state = { ...this.state, ...e.state };
                this.renderView(false);
            }
        });

        // Load initial view
        this.loadHome();
    },

    /**
     * Get version from API
     */
    async getVersion() {
        try {
            const data = await this.fetchData('getVersion');
            if (data.success) {
                this.state.version = data.data.version;
                this.state.webname = data.data.webname;
                document.getElementById('version').textContent = 'v.' + this.state.version;
                document.getElementById('webname').textContent = this.state.webname || 'Mida Blue Book';
            }
        } catch (e) {
            console.error('Error getting version:', e);
        }
    },

    /**
     * Fetch data from API
     */
    async fetchData(action, params = {}) {
        const url = new URL(this.apiBase, window.location.origin + window.location.pathname.replace(/[^/]*$/, ''));
        url.searchParams.set('action', action);
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });

        const response = await fetch(url);
        return response.json();
    },

    /**
     * Show loading state
     */
    showLoading() {
        document.getElementById('content').innerHTML = `
            <div class="loading">
                <div class="loading-spinner"></div>
                <span>กำลังโหลด...</span>
            </div>
        `;
    },

    /**
     * Update breadcrumb
     */
    updateBreadcrumb() {
        const breadcrumb = document.getElementById('breadcrumb');
        let html = `
            <a href="#" class="breadcrumb-item ${this.state.currentView === 'home' ? 'active' : ''}" onclick="BluebookApp.loadHome(); return false;">
                <i class="fas fa-home"></i> หน้าแรก
            </a>
        `;

        if (this.state.cid) {
            const typeName = this.state.cid == '1' ? 'รถกระบะ' : 'รถเก๋ง';
            html += `<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>`;
            html += `<a href="#" class="breadcrumb-item ${this.state.currentView === 'brands' ? 'active' : ''}" onclick="BluebookApp.loadBrands('${this.state.cid}'); return false;">${typeName}</a>`;
        }

        if (this.state.bid) {
            html += `<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>`;
            html += `<a href="#" class="breadcrumb-item ${this.state.currentView === 'models' ? 'active' : ''}" onclick="BluebookApp.loadModels('${this.state.cid}', '${this.state.bid}'); return false;">${this.state.bid}</a>`;
        }

        if (this.state.mid) {
            html += `<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>`;
            html += `<a href="#" class="breadcrumb-item ${this.state.currentView === 'years' ? 'active' : ''}" onclick="BluebookApp.loadYears('${this.state.cid}', '${this.state.bid}', '${this.state.mid}'); return false;">${this.state.mid}</a>`;
        }

        if (this.state.yy) {
            html += `<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>`;
            html += `<a href="#" class="breadcrumb-item ${this.state.currentView === 'submodels' ? 'active' : ''}" onclick="BluebookApp.loadSubModels('${this.state.cid}', '${this.state.bid}', '${this.state.mid}', '${this.state.yy}'); return false;">ปี ${this.state.yy}</a>`;
        }

        if (this.state.carid && this.state.currentView === 'price') {
            html += `<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>`;
            html += `<span class="breadcrumb-item active">ราคา</span>`;
        }

        breadcrumb.innerHTML = html;
    },

    /**
     * Push state to history
     */
    pushState(view) {
        const state = { ...this.state, currentView: view };
        const url = new URL(window.location.href);
        url.hash = this.getHashFromState(state);
        history.pushState(state, '', url);
    },

    getHashFromState(state) {
        let hash = state.currentView;
        if (state.cid) hash += '/' + state.cid;
        if (state.bid) hash += '/' + encodeURIComponent(state.bid);
        if (state.mid) hash += '/' + encodeURIComponent(state.mid);
        if (state.yy) hash += '/' + state.yy;
        if (state.carid) hash += '/' + state.carid;
        return hash;
    },

    /**
     * Load home view
     */
    async loadHome() {
        this.state.currentView = 'home';
        this.state.cid = null;
        this.state.bid = null;
        this.state.mid = null;
        this.state.yy = null;
        this.state.carid = null;

        this.showLoading();
        this.updateBreadcrumb();

        try {
            const data = await this.fetchData('getTypes');
            if (data.success) {
                this.renderHome(data.data);
                this.pushState('home');
            }
        } catch (e) {
            console.error('Error loading home:', e);
        }
    },

    renderHome(types) {
        const content = document.getElementById('content');
        let html = `
            <div class="section-title" style="margin-top: 2rem;">
                <h2>เลือกประเภทรถ</h2>
                <p>กรุณาเลือกประเภทรถที่ต้องการค้นหาราคา</p>
            </div>
            <div class="cards-grid cards-grid-2">
        `;

        types.forEach(type => {
            html += `
                <div class="card card-type" onclick="BluebookApp.loadBrands('${type.id}')">
                    <div class="card-icon">
                        <i class="fas ${type.icon}"></i>
                    </div>
                    <h3 class="card-title">${type.name}</h3>
                </div>
            `;
        });

        html += '</div>';
        content.innerHTML = html;
        content.classList.add('content-fade');
        setTimeout(() => content.classList.remove('content-fade'), 300);
    },

    /**
     * Load brands
     */
    async loadBrands(cid) {
        this.state.cid = cid;
        this.state.bid = null;
        this.state.mid = null;
        this.state.yy = null;
        this.state.carid = null;
        this.state.currentView = 'brands';

        this.showLoading();
        this.updateBreadcrumb();

        try {
            const data = await this.fetchData('getBrands', { cid });
            if (data.success) {
                this.renderBrands(data.data);
                this.pushState('brands');
            }
        } catch (e) {
            console.error('Error loading brands:', e);
        }
    },

    renderBrands(brands) {
        const content = document.getElementById('content');
        const typeName = this.state.cid == '1' ? 'รถกระบะ' : 'รถเก๋ง';

        let html = `
            <button class="back-btn" onclick="BluebookApp.loadHome()">
                <i class="fas fa-arrow-left"></i> กลับ
            </button>
            <div class="section-title mt-4">
                <h2><i class="fas ${this.state.cid == '1' ? 'fa-truck-pickup' : 'fa-car'}"></i> ${typeName}</h2>
                <p>เลือกยี่ห้อรถ</p>
            </div>
            <div class="cards-grid">
        `;

        brands.forEach(brand => {
            html += `
                <div class="card" onclick="BluebookApp.loadModels('${this.state.cid}', '${this.escapeHtml(brand.name)}')">
                    <div class="card-icon">
                        <img src="icons/${brand.icon}" alt="${brand.name}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-car\\'></i>'">
                    </div>
                    <h3 class="card-title">${brand.name}</h3>
                </div>
            `;
        });

        html += '</div>';
        content.innerHTML = html;
        content.classList.add('content-fade');
        setTimeout(() => content.classList.remove('content-fade'), 300);
    },

    /**
     * Load models
     */
    async loadModels(cid, bid) {
        this.state.cid = cid;
        this.state.bid = bid;
        this.state.mid = null;
        this.state.yy = null;
        this.state.carid = null;
        this.state.currentView = 'models';

        this.showLoading();
        this.updateBreadcrumb();

        try {
            const data = await this.fetchData('getModels', { cid, bid });
            if (data.success) {
                this.renderModels(data.data);
                this.pushState('models');
            }
        } catch (e) {
            console.error('Error loading models:', e);
        }
    },

    renderModels(models) {
        const content = document.getElementById('content');
        const typeName = this.state.cid == '1' ? 'รถกระบะ' : 'รถเก๋ง';

        let html = `
            <button class="back-btn" onclick="BluebookApp.loadBrands('${this.state.cid}')">
                <i class="fas fa-arrow-left"></i> กลับ
            </button>
            <div class="section-title mt-4">
                <h2><i class="fas fa-car-side"></i> ${this.state.bid}</h2>
                <p>${typeName} - เลือกรุ่น</p>
            </div>
            <div class="cards-grid">
        `;

        if (models.length === 0) {
            html += `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-search"></i>
                    <p>ไม่พบข้อมูลรุ่นรถ</p>
                </div>
            `;
        } else {
            models.forEach(model => {
                html += `
                    <div class="card" onclick="BluebookApp.loadYears('${this.state.cid}', '${this.escapeHtml(this.state.bid)}', '${this.escapeHtml(model.name)}')">
                        <div class="card-icon">
                            <i class="fas ${this.state.cid == '1' ? 'fa-truck-pickup' : 'fa-car-side'}"></i>
                        </div>
                        <h3 class="card-title">${model.name}</h3>
                    </div>
                `;
            });
        }

        html += '</div>';
        content.innerHTML = html;
        content.classList.add('content-fade');
        setTimeout(() => content.classList.remove('content-fade'), 300);
    },

    /**
     * Load years
     */
    async loadYears(cid, bid, mid) {
        this.state.cid = cid;
        this.state.bid = bid;
        this.state.mid = mid;
        this.state.yy = null;
        this.state.carid = null;
        this.state.currentView = 'years';

        this.showLoading();
        this.updateBreadcrumb();

        try {
            const data = await this.fetchData('getYears', { cid, bid, mid });
            if (data.success) {
                this.renderYears(data.data);
                this.pushState('years');
            }
        } catch (e) {
            console.error('Error loading years:', e);
        }
    },

    renderYears(years) {
        const content = document.getElementById('content');

        let html = `
            <button class="back-btn" onclick="BluebookApp.loadModels('${this.state.cid}', '${this.escapeHtml(this.state.bid)}')">
                <i class="fas fa-arrow-left"></i> กลับ
            </button>
            <div class="section-title mt-4">
                <h2><i class="fas fa-calendar-alt"></i> ${this.state.bid} ${this.state.mid}</h2>
                <p>เลือกปี</p>
            </div>
            <div class="cards-grid">
        `;

        if (years.length === 0) {
            html += `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-search"></i>
                    <p>ไม่พบข้อมูลปี</p>
                </div>
            `;
        } else {
            years.forEach(year => {
                html += `
                    <div class="card" onclick="BluebookApp.loadSubModels('${this.state.cid}', '${this.escapeHtml(this.state.bid)}', '${this.escapeHtml(this.state.mid)}', '${year.year}')">
                        <div class="card-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <h3 class="card-title">${year.year}</h3>
                    </div>
                `;
            });
        }

        html += '</div>';
        content.innerHTML = html;
        content.classList.add('content-fade');
        setTimeout(() => content.classList.remove('content-fade'), 300);
    },

    /**
     * Load submodels
     */
    async loadSubModels(cid, bid, mid, yy) {
        this.state.cid = cid;
        this.state.bid = bid;
        this.state.mid = mid;
        this.state.yy = yy;
        this.state.carid = null;
        this.state.currentView = 'submodels';

        this.showLoading();
        this.updateBreadcrumb();

        try {
            const data = await this.fetchData('getSubModels', { cid, bid, mid, yy });
            if (data.success) {
                this.renderSubModels(data.data);
                this.pushState('submodels');
            }
        } catch (e) {
            console.error('Error loading submodels:', e);
        }
    },

    renderSubModels(submodels) {
        const content = document.getElementById('content');

        let html = `
            <button class="back-btn" onclick="BluebookApp.loadYears('${this.state.cid}', '${this.escapeHtml(this.state.bid)}', '${this.escapeHtml(this.state.mid)}')">
                <i class="fas fa-arrow-left"></i> กลับ
            </button>
            <div class="section-title mt-4">
                <h2><i class="fas fa-cogs"></i> ${this.state.bid} ${this.state.mid} ปี ${this.state.yy}</h2>
                <p>เลือกรุ่นย่อย</p>
            </div>
            <div class="cards-grid">
        `;

        if (submodels.length === 0) {
            html += `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-search"></i>
                    <p>ไม่พบข้อมูลรุ่นย่อย</p>
                </div>
            `;
        } else {
            submodels.forEach(sub => {
                html += `
                    <div class="card" onclick="BluebookApp.loadPrice('${this.state.cid}', '${this.escapeHtml(this.state.bid)}', '${this.escapeHtml(this.state.mid)}', '${this.state.yy}', '${sub.id}')">
                        <div class="card-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3 class="card-title">${sub.submodel}</h3>
                        <p class="card-subtitle">
                            <i class="fas fa-cog"></i> เกียร์ ${sub.gear}
                            ${sub.hasPicture ? '<i class="fas fa-image" style="margin-left: 10px;"></i>' : ''}
                        </p>
                        <p class="card-subtitle" style="color: var(--accent-dark); font-weight: 700; font-size: 1.25rem; margin-top: 8px;">
                            <i class="fas fa-coins"></i> ${this.formatNumber(sub.price)}
                        </p>
                    </div>
                `;
            });
        }

        html += '</div>';
        content.innerHTML = html;
        content.classList.add('content-fade');
        setTimeout(() => content.classList.remove('content-fade'), 300);
    },

    /**
     * Load price detail
     */
    async loadPrice(cid, bid, mid, yy, carid) {
        this.state.cid = cid;
        this.state.bid = bid;
        this.state.mid = mid;
        this.state.yy = yy;
        this.state.carid = carid;
        this.state.currentView = 'price';

        this.showLoading();
        this.updateBreadcrumb();

        try {
            const data = await this.fetchData('getPrice', { carid });
            if (data.success) {
                this.renderPrice(data.data);
                this.pushState('price');
            }
        } catch (e) {
            console.error('Error loading price:', e);
        }
    },

    renderPrice(car) {
        const content = document.getElementById('content');
        const typeName = this.state.cid == '1' ? 'รถกระบะ' : 'รถเก๋ง';

        let html = `
            <button class="back-btn" onclick="BluebookApp.loadSubModels('${this.state.cid}', '${this.escapeHtml(this.state.bid)}', '${this.escapeHtml(this.state.mid)}', '${this.state.yy}')">
                <i class="fas fa-arrow-left"></i> กลับ
            </button>
            
            <div class="price-page">
                <!-- Car Info Header -->
                <div class="price-header">
                    <div class="price-header-badge">
                        <i class="fas ${this.state.cid == '1' ? 'fa-truck-pickup' : 'fa-car'}"></i>
                        ${typeName}
                    </div>
                    <h1 class="price-header-title">${this.state.bid}</h1>
                    <p class="price-header-subtitle">${this.state.mid} | ปี ${this.state.yy}</p>
                </div>
                
                ${car.picture ? `
                    <div class="price-image-container">
                        <img src="${car.picture}" alt="Car Image" class="price-image" onerror="this.parentElement.style.display='none'">
                    </div>
                ` : ''}
                
                <!-- Price Calculator Card -->
                <div class="price-calculator">
                    <div class="price-calculator-header">
                        <i class="fas fa-calculator"></i>
                        <span>คำนวณยอดจัด</span>
                    </div>
                    <div class="price-calculator-body">
                        <div class="price-rate-selector">
                            <label>เลือกเปอร์เซ็นต์</label>
                            <div class="rate-buttons" id="rateButtons">
                                <button class="rate-btn" data-rate="120">120%</button>
                                <button class="rate-btn" data-rate="110">110%</button>
                                <button class="rate-btn active" data-rate="100">100%</button>
                                <button class="rate-btn" data-rate="90">90%</button>
                                <button class="rate-btn" data-rate="85">85%</button>
                                <button class="rate-btn" data-rate="80">80%</button>
                                <button class="rate-btn" data-rate="75">75%</button>
                                <button class="rate-btn" data-rate="70">70%</button>
                            </div>
                        </div>
                        <div class="price-result">
                            <div class="price-result-label">ยอดจัด</div>
                            <div class="price-result-value" id="priceDisplay">${this.formatNumber(car.price)}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Car Details -->
                <div class="price-details">
                    <div class="price-detail-item">
                        <div class="price-detail-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div class="price-detail-content">
                            <span class="price-detail-label">รหัสรถ</span>
                            <span class="price-detail-value">${car.code}</span>
                        </div>
                    </div>
                    <div class="price-detail-item">
                        <div class="price-detail-icon" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <div class="price-detail-content">
                            <span class="price-detail-label">รุ่นย่อย</span>
                            <span class="price-detail-value">${car.submodel}</span>
                        </div>
                    </div>
                    <div class="price-detail-item">
                        <div class="price-detail-icon" style="background: linear-gradient(135deg, #10b981, #14b8a6);">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="price-detail-content">
                            <span class="price-detail-label">ระบบเกียร์</span>
                            <span class="price-detail-value">${car.gear}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        content.innerHTML = html;
        content.classList.add('content-fade');
        setTimeout(() => content.classList.remove('content-fade'), 300);

        // Add rate button event listeners
        const basePrice = car.price;
        document.querySelectorAll('.rate-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.rate-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const rate = this.dataset.rate;
                const calculatedPrice = Math.floor((basePrice * rate / 100) / 10000) * 10000;
                document.getElementById('priceDisplay').textContent = BluebookApp.formatNumber(calculatedPrice);
            });
        });
    },

    /**
     * Update price based on rate
     */
    updatePrice(basePrice) {
        const rate = document.getElementById('rateSelect').value;
        const calculatedPrice = Math.floor((basePrice * rate / 100) / 10000) * 10000;
        document.getElementById('priceDisplay').textContent = '฿' + this.formatNumber(calculatedPrice);
    },

    /**
     * Render current view
     */
    renderView(updateHistory = true) {
        switch (this.state.currentView) {
            case 'home':
                this.loadHome();
                break;
            case 'brands':
                this.loadBrands(this.state.cid);
                break;
            case 'models':
                this.loadModels(this.state.cid, this.state.bid);
                break;
            case 'years':
                this.loadYears(this.state.cid, this.state.bid, this.state.mid);
                break;
            case 'submodels':
                this.loadSubModels(this.state.cid, this.state.bid, this.state.mid, this.state.yy);
                break;
            case 'price':
                this.loadPrice(this.state.cid, this.state.bid, this.state.mid, this.state.yy, this.state.carid);
                break;
        }
    },

    /**
     * Utility: Format number with commas
     */
    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    /**
     * Utility: Escape HTML
     */
    escapeHtml(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    BluebookApp.init();
});
