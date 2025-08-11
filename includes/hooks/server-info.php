<?php
use WHMCS\View\Menu\Item as MenuItem;
use Illuminate\Database\Capsule\Manager as Capsule;

add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar) {
    $service = Menu::context('service');

    if ($service) {
        $username = $service->username;
        $domain = $service->domain;
        $encryptedPassword = $service->password;
        $serverid = $service->server;

        $server = Capsule::table('tblservers')->where('id', $serverid)->first();
        $password = decrypt($encryptedPassword);

        if (!empty($username)) {
            // Puffx Host Branding Header
            $secondarySidebar->addChild('puffxBranding', [
                'label' => '🌐 Puffx Host — Client Panel',
                'uri' => 'https://puffxhost.com',
                'icon' => 'fa-cloud',
                'order' => 0,
                'class' => 'puffx-branding'
            ]);

            // Service Information
            $secondarySidebar->addChild('credentials', [
                'label' => 'Service Information',
                'uri' => '#',
                'icon' => 'fa-desktop',
            ]);
            $credentialPanel = $secondarySidebar->getChild('credentials');
            $credentialPanel->moveToBack();
            $credentialPanel->addChild('username', [
                'label' => "Username: $username",
                'order' => 1,
                'icon' => 'fa-user',
                'class' => 'custom-username'
            ]);
            $credentialPanel->addChild('password', [
                'label' => "Password: $password",
                'order' => 2,
                'icon' => 'fa-lock',
                'class' => 'custom-password'
            ]);
            $credentialPanel->addChild('domain', [
                'label' => "Domain: $domain",
                'order' => 3,
                'icon' => 'fa-globe',
                'class' => 'custom-domain'
            ]);

            if ($server) {
                $secondarySidebar->addChild('serverInfo', [
                    'label' => 'Server Information',
                    'uri' => '#',
                    'icon' => 'fa-server',
                ]);

                $serverInfoPanel = $secondarySidebar->getChild('serverInfo');
                $serverInfoPanel->addChild('hostname', [
                    'label' => "Hostname: $server->hostname",
                    'order' => 1,
                    'icon' => 'fa-server',
                    'class' => 'custom-hostname'
                ]);
                $serverInfoPanel->addChild('ip', [
                    'label' => "IP Address: $server->ipaddress",
                    'order' => 2,
                    'icon' => 'fa-info',
                    'class' => 'custom-ip'
                ]);
                $serverInfoPanel->addChild('name1', [
                    'label' => "Nameserver 1: $server->nameserver1",
                    'order' => 3,
                    'icon' => 'fa-info-circle',
                    'class' => 'custom-nameserver',
                    'onclick' => "copyTextToClipboard('{$server->nameserver1}');",
                ]);
                $serverInfoPanel->addChild('name2', [
                    'label' => "Nameserver 2: $server->nameserver2",
                    'order' => 4,
                    'icon' => 'fa-info-circle',
                    'class' => 'custom-nameserver',
                    'onclick' => "copyTextToClipboard('{$server->nameserver2}');",
                ]);
            }
        }
    }
});
?>

<script>
function copyTextToClipboard(text) {
    var textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
        document.execCommand('copy');
        alert('Copied: ' + text);
    } catch (err) {
        console.error('Unable to copy', err);
    }

    document.body.removeChild(textarea);
}
</script>

<style>
/* Puffx Host Branding Style */
.puffx-branding {
    background: linear-gradient(90deg, #0056b3, #00aaff);
    color: #fff !important;
    padding: 12px;
    font-weight: bold;
    font-size: 15px;
    text-align: center;
    border-radius: 5px;
}
.puffx-branding:hover {
    background: linear-gradient(90deg, #004099, #0090dd);
}

/* Custom fields styling */
.custom-username, .custom-password, .custom-domain, 
.custom-hostname, .custom-ip, .custom-nameserver {
    padding: 8px;
    margin: 5px 0;
    background-color: #f7faff;
    border-left: 4px solid #007bff;
    border-radius: 4px;
    font-size: 13px;
    font-weight: bold;
    color: #333;
}
.custom-username:hover, .custom-password:hover, 
.custom-domain:hover, .custom-hostname:hover, 
.custom-ip:hover, .custom-nameserver:hover {
    background-color: #e8f2ff;
    cursor: pointer;
}
.fa-user, .fa-lock, .fa-globe, .fa-server, .fa-info, .fa-info-circle {
    margin-right: 6px;
    color: #007bff;
}
</style>
