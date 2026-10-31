<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\View\Menu\Item as MenuItem;

/**
 * Add the current service's connection details to the client-area sidebar.
 * The service context is supplied by WHMCS for the service details page.
 */
add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar) {
    $service = Menu::context('service');
    if (!$service) {
        return;
    }

    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };

    $username = (string) ($service->username ?? '');
    $domain = (string) ($service->domain ?? '');
    $password = '';
    $encryptedPassword = $service->password ?? '';
    if ($encryptedPassword !== '') {
        try {
            $password = (string) decrypt($encryptedPassword);
        } catch (Throwable $exception) {
            // A missing or invalid service password must not break the client area.
            $password = '';
        }
    }

    $server = null;
    $serverId = (int) ($service->server ?? 0);
    if ($serverId > 0) {
        $server = Capsule::table('tblservers')->where('id', $serverId)->first();
    }

    $GLOBALS['puffxServerInfoWidget'] = [
        'password' => $password,
    ];

    $secondarySidebar->addChild('puffxBranding', [
        'label' => '🌐 Puffx Host — Client Panel',
        'uri' => 'https://puffxhost.com',
        'icon' => 'fa-cloud',
        'order' => 0,
        'class' => 'puffx-branding',
    ]);

    $secondarySidebar->addChild('credentials', [
        'label' => 'Service Information',
        'uri' => '#',
        'icon' => 'fa-desktop',
    ]);
    $credentialPanel = $secondarySidebar->getChild('credentials');
    $credentialPanel->moveToBack();

    $credentialPanel->addChild('username', [
        'label' => 'Username: ' . $escape($username !== '' ? $username : 'Not available'),
        'order' => 1,
        'icon' => 'fa-user',
        'class' => 'custom-username',
    ]);
    $credentialPanel->addChild('password', [
        'label' => 'Password: ' . ($password !== '' ? '••••••••' : 'Not available'),
        'uri' => '#',
        'order' => 2,
        'icon' => 'fa-lock',
        'class' => 'custom-password',
    ]);
    $credentialPanel->addChild('domain', [
        'label' => 'Domain: ' . $escape($domain !== '' ? $domain : 'Not available'),
        'order' => 3,
        'icon' => 'fa-globe',
        'class' => 'custom-domain',
    ]);

    if ($server) {
        $secondarySidebar->addChild('serverInfo', [
            'label' => 'Server Information',
            'uri' => '#',
            'icon' => 'fa-server',
        ]);

        $serverInfoPanel = $secondarySidebar->getChild('serverInfo');
        $serverInfoPanel->addChild('hostname', [
            'label' => 'Hostname: ' . $escape($server->hostname ?? 'Not available'),
            'order' => 1,
            'icon' => 'fa-server',
            'class' => 'custom-copyable custom-hostname',
        ]);
        $serverInfoPanel->addChild('ip', [
            'label' => 'IP Address: ' . $escape($server->ipaddress ?? 'Not available'),
            'order' => 2,
            'icon' => 'fa-info',
            'class' => 'custom-copyable custom-ip',
        ]);

        $nameservers = [
            1 => (string) ($server->nameserver1 ?? ''),
            2 => (string) ($server->nameserver2 ?? ''),
        ];
        foreach ($nameservers as $number => $nameserver) {
            if ($nameserver === '') {
                continue;
            }

            $serverInfoPanel->addChild('nameserver' . $number, [
                'label' => 'Nameserver ' . $number . ': ' . $escape($nameserver),
                'uri' => '#',
                'order' => $number + 2,
                'icon' => 'fa-info-circle',
                'class' => 'custom-copyable custom-nameserver',
            ]);
        }
    }
});

/**
 * Add progressive, accessible actions only on pages where this widget exists.
 * Copy targets are read from rendered text, so WHMCS values never enter inline
 * JavaScript or event-handler attributes.
 */
add_hook('ClientAreaFooterOutput', 1, function () {
    if (empty($GLOBALS['puffxServerInfoWidget'])) {
        return '';
    }

    $payload = json_encode(
        $GLOBALS['puffxServerInfoWidget'],
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    );
    if ($payload === false) {
        return '';
    }

    return <<<HTML
<style>
.puffx-branding {
    background: linear-gradient(90deg, #0056b3, #00aaff);
    color: #fff !important;
    padding: 12px;
    font-weight: 700;
    font-size: 15px;
    text-align: center;
    border-radius: 5px;
}
.puffx-branding:hover { background: linear-gradient(90deg, #004099, #0090dd); }
.custom-username, .custom-password, .custom-domain,
.custom-hostname, .custom-ip, .custom-nameserver {
    padding: 8px;
    margin: 5px 0;
    background-color: #f7faff;
    border-left: 4px solid #007bff;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    color: #263445;
}
.custom-copyable a { cursor: pointer; }
.puffx-widget-actions { display: inline-flex; gap: 6px; margin: 4px 0 6px 26px; }
.puffx-widget-action {
    border: 1px solid #b8c9dc;
    border-radius: 4px;
    padding: 4px 8px;
    background: #fff;
    color: #174d80;
    font: inherit;
    font-size: 12px;
    cursor: pointer;
}
.puffx-widget-action:hover, .puffx-widget-action:focus { background: #e8f2ff; }
.puffx-widget-status { margin: 4px 0 4px 8px; color: #31566f; font-size: 12px; }
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; } }
</style>
<script>
(function () {
    'use strict';
    var widget = {$payload};
    var status = document.createElement('span');
    status.className = 'puffx-widget-status';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');

    function announce(message) {
        status.textContent = message;
    }

    function legacyCopy(value) {
        var field = document.createElement('textarea');
        field.value = value;
        field.setAttribute('readonly', 'readonly');
        field.style.position = 'fixed';
        field.style.opacity = '0';
        document.body.appendChild(field);
        field.select();
        var copied = false;
        try { copied = document.execCommand('copy'); } catch (error) { copied = false; }
        field.remove();
        return copied;
    }

    function copyValue(value, label) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(function () {
                announce(label + ' copied.');
            }).catch(function () {
                announce(legacyCopy(value) ? label + ' copied.' : 'Copy failed. Select the value and copy it manually.');
            });
            return;
        }
        announce(legacyCopy(value) ? label + ' copied.' : 'Copy failed. Select the value and copy it manually.');
    }

    function addButton(container, label, onClick) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'puffx-widget-action';
        button.textContent = label;
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            onClick(button);
        });
        container.appendChild(button);
        return button;
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.custom-copyable a, a.custom-copyable');
        if (!link) return;
        event.preventDefault();
        var label = link.textContent || '';
        var separator = label.indexOf(':');
        var value = separator >= 0 ? label.slice(separator + 1).trim() : label.trim();
        if (value && value !== 'Not available') copyValue(value, 'Server detail');
    });

    var passwordLink = document.querySelector('.custom-password a, a.custom-password');
    if (passwordLink && widget.password) {
        var passwordItem = passwordLink.parentElement;
        var controls = document.createElement('span');
        controls.className = 'puffx-widget-actions';
        passwordLink.textContent = 'Password: ••••••••';
        passwordLink.addEventListener('click', function (event) { event.preventDefault(); });
        var toggleButton = addButton(controls, 'Show', function (button) {
            var expanded = button.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                passwordLink.textContent = 'Password: ••••••••';
                button.textContent = 'Show';
                button.setAttribute('aria-label', 'Show service password');
                button.setAttribute('aria-expanded', 'false');
            } else {
                passwordLink.textContent = 'Password: ' + widget.password;
                button.textContent = 'Hide';
                button.setAttribute('aria-label', 'Hide service password');
                button.setAttribute('aria-expanded', 'true');
            }
        });
        toggleButton.setAttribute('aria-label', 'Show service password');
        toggleButton.setAttribute('aria-expanded', 'false');
        addButton(controls, 'Copy', function () { copyValue(widget.password, 'Password'); });
        passwordItem.appendChild(controls);
    }

    var statusItem = document.querySelector('.custom-copyable, .custom-password');
    if (statusItem) {
        var statusHost = statusItem.closest('li') || statusItem.parentElement;
        if (statusHost) statusHost.appendChild(status);
    }
})();
</script>
HTML;
});
