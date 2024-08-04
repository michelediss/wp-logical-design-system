document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('wp-admin-bar-compile_scss').addEventListener('click', function(e) {
        e.preventDefault(); // Prevent the default action

        // Display a loading message or spinner if needed
        document.querySelector('#wp-admin-bar-compile_scss .ab-item').textContent = 'Compiling...';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', scss_compilation.ajax_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Display success message
                    document.querySelector('#wp-admin-bar-compile_scss .ab-item').textContent = 'Compile SCSS';
                    alert(response.data); // Optionally, show a success alert
                } else {
                    // Display error message
                    document.querySelector('#wp-admin-bar-compile_scss .ab-item').textContent = 'Compile SCSS';
                    alert(response.data); // Optionally, show an error alert
                }
            } else {
                // Display error message
                document.querySelector('#wp-admin-bar-compile_scss .ab-item').textContent = 'Compile SCSS';
                alert('An error occurred while processing the request.');
            }
        };

        xhr.onerror = function() {
            document.querySelector('#wp-admin-bar-compile_scss .ab-item').textContent = 'Compile SCSS';
            alert('An error occurred while processing the request.');
        };

        var params = 'action=compile_scss&security=' + encodeURIComponent(scss_compilation.nonce);
        xhr.send(params);
    });
});
