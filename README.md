# WHMCS Nameserver Details Widget

A small WHMCS hook that puts the current service's login and assigned server details in the client-area sidebar.

## What it does

- Reads the server assigned to the service being viewed in WHMCS.
- Shows the service username and domain, plus the server hostname, IP address, and configured nameservers.
- Masks the service password by default. The client can reveal it temporarily or copy it with a button.
- Makes hostname, IP address, and nameserver rows copyable without JavaScript event code in database-backed values.
- Gives copy feedback without a blocking browser alert, and includes a fallback for browsers without the Clipboard API.
- Escapes values before placing them in menu labels and skips nameservers that are not configured.

The widget appears on a service details page where WHMCS provides a `service` context. It does not query every service on the client account.

## Requirements

- WHMCS 8 or later
- PHP 7.4 or later
- A client service assigned to a WHMCS server

## Install

1. Download or clone this repository:

   ```sh
   git clone https://github.com/puffxhost/whmcs-nameserver-details-widget.git
   ```

2. Upload [`includes/hooks/server-info.php`](includes/hooks/server-info.php) to your WHMCS installation's `includes/hooks/` directory.
3. In WHMCS, empty the template cache under **Utilities → System → System Cleanup → Empty Template Cache**.
4. Sign in to a client account, open **Services → My Services**, then view a service that has a server assigned. The information panels appear in the client-area sidebar.

## Example

Values below are documentation placeholders. The widget reads live values from the service and its assigned server in WHMCS.

```text
Service Information
Username: example_user
Password: ••••••••  [Show] [Copy]
Domain: example.net

Server Information
Hostname: host.example.net
IP Address: 192.0.2.10
Nameserver 1: ns1.example.net  (click to copy)
Nameserver 2: ns2.example.net  (click to copy)
```

## Password handling

The password is hidden visually until the client selects **Show**. WHMCS still has to send the password to that authenticated client-area page so the reveal and copy controls can work; masking is a privacy convenience, not a security boundary. Use HTTPS, restrict access to the WHMCS client area, and do not add real customer credentials to documentation, screenshots, or source control.

If the service password is empty or cannot be decrypted, the widget displays **Not available** and keeps the rest of the service details working.

## Contributing

Please open an issue or pull request with the WHMCS version and theme involved. Never include customer credentials, private IPs, or production server details in examples.

## License

MIT. See [LICENSE](LICENSE).
