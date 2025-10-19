jQuery(document).ready(function($) {
     var pluginSlug = 'spice-starter-sites'; // Adjust this based on your plugin slug
    var pluginUrl = 'https://spicethemes.com/extensions/spice-starter-sites.zip'; // The plugin URL

    // Check the plugin status when the page loads
    $.ajax({
        url: pluginInstallerAjax.ajax_url,
        method: 'POST',
        data: {
            action: 'newsblogger_check_plugin_status',
            plugin_slug: pluginSlug,
            _ajax_nonce: pluginInstallerAjax.nonce
        },
        success: function(response) {
            var $button = $('#install-plugin-button');

            if (response.success) {
                if (response.data.status === 'not_installed') {
                    $button.text('Install Plugin').attr('disabled', false);
                } else if (response.data.status === 'installed') {
                    $button.text('Activate Plugin').attr('disabled', false);
                } else if (response.data.status === 'activated') {
                    $button.text('Activated').attr('disabled', true);
                }
            } else {
                alert('Error: ' + response.data);
                $button.attr('disabled', true);
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });

    // Handle plugin installation and activation on button click
    $('#install-plugin-button').on('click', function(e) {
        e.preventDefault();

        var $button = $(this);

        if ($button.text() === 'Install Plugin') {
            $button.text('Installing...').attr('disabled', true);

            // Install the plugin
            $.ajax({
                url: pluginInstallerAjax.ajax_url,
                method: 'POST',
                data: {
                    action: 'newsblogger_install_activate_plugin',
                    plugin_url: pluginUrl,
                    plugin_slug: pluginSlug,
                    _ajax_nonce: pluginInstallerAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.text('Activate Plugin').attr('disabled', false);

                        // Redirect after activation
                        $button.on('click', function() {
                            $button.text('Activating...').attr('disabled', true);

                            // Delay the redirect
                            setTimeout(function() {
                                window.location.href = response.data.redirect_url;
                            }, 6000); // Adjust time in milliseconds (3000 = 3 seconds)
                        });
                    } else {
                        alert('Error: ' + response.data);
                        $button.text('Install Plugin').attr('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.text('Install Plugin').attr('disabled', false);
                }
            });
        } else if ($button.text() === 'Activate Plugin') {
            $button.text('Activating...').attr('disabled', true);

            // Redirect after activation
            $.ajax({
                url: pluginInstallerAjax.ajax_url,
                method: 'POST',
                data: {
                    action: 'newsblogger_install_activate_plugin',
                    plugin_url: pluginUrl,
                    plugin_slug: pluginSlug,
                    _ajax_nonce: pluginInstallerAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Delay the redirect
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 6000); // Adjust time in milliseconds (3000 = 3 seconds)
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
});
