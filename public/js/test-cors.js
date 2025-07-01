// Simple CORS test script
document.addEventListener('DOMContentLoaded', () => {
    // Create a div for output
    const output = document.createElement('div');
    output.id = 'cors-test-output';
    output.style.margin = '20px';
    output.style.padding = '20px';
    output.style.backgroundColor = '#f5f5f5';
    output.style.borderRadius = '5px';
    output.style.fontFamily = 'monospace';
    document.body.appendChild(output);
    
    // Log function
    const log = (message, isError = false) => {
        const entry = document.createElement('div');
        entry.style.marginBottom = '10px';
        entry.style.color = isError ? 'red' : 'green';
        entry.textContent = message;
        output.appendChild(entry);
        console.log(message);
    };
    
    // Get current origin
    const origin = window.location.origin;
    log(`Current origin: ${origin}`);
    
    // Test different URL formats
    const testUrls = [
        '/storage/work_orders/sample.pdf',  // Relative path
        `${origin}/storage/work_orders/sample.pdf`,  // Full URL with same origin
        'http://localhost/storage/work_orders/sample.pdf', // localhost URL
        'http://127.0.0.1:8000/storage/work_orders/sample.pdf' // 127.0.0.1 URL
    ];
    
    // Test each URL
    testUrls.forEach(url => {
        log(`Testing URL: ${url}`);
        
        fetch(url)
            .then(response => {
                if (response.ok) {
                    log(`✅ Success: ${url}`, false);
                    return response.blob();
                } else {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
            })
            .then(blob => {
                log(`✅ File loaded: ${url} (${blob.size} bytes)`, false);
            })
            .catch(error => {
                log(`❌ Error: ${url} - ${error.message}`, true);
            });
    });
});
