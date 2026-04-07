<?php
// Test database connection and stored function
include("includes/config.php");

echo "<h2>Database Connection Test</h2>";

if ($objConnect) {
    echo "<p style='color:green'>✅ Connected to database successfully!</p>";

    // Test stored function
    echo "<h3>Test Fnc_LoanProtect_Rate Function</h3>";

    // Try calling the function with sample parameters
    $testSql = "SELECT Fnc_LoanProtect_Rate('TLIFE', 'male', '60', '35', '19900101') AS RATE";
    echo "<p>Query: <code>" . htmlspecialchars($testSql) . "</code></p>";

    $result = mysqli_query($objConnect, $testSql);

    if ($result) {
        echo "<p style='color:green'>✅ Query executed successfully!</p>";
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            echo "<p><strong>Result RATE:</strong> " . $row['RATE'] . "</p>";
        } else {
            echo "<p style='color:orange'>⚠️ No result returned</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Query Error: " . mysqli_error($objConnect) . "</p>";
    }

    mysqli_close($objConnect);
} else {
    echo "<p style='color:red'>❌ Connection failed!</p>";
}
?>