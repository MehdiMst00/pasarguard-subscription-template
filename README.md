# Client-Only PasarGuard Template

This repository is a fork of the [PasarGuard Subscription Template](https://github.com/PasarGuard/subscription-template) 
.
It can be deployed on a PHP server to proxy PasarGuard subscription requests and host the PasarGuard User Dashboard (React UI).

<p align="center"> <img src="https://raw.githubusercontent.com/PasarGuard/subscription-template/refs/heads/main/screenshots/en.png" alt="English UI" width="40%"> <img src="https://raw.githubusercontent.com/PasarGuard/subscription-template/refs/heads/main/screenshots/fa.png" alt="Persian UI" width="30%"> </p>


## 📦 Installation

**1. `.htaccess`**

```
RewriteEngine On

# 1. Remove Trailing Slash (New Rule)
# If request ends with /, redirect to version without /. 
# Condition !-d ensures we don't break physical directories.
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [L,R=301]

# 2. Serve Real Files Directly
# If the request matches a real file (React assets, images), serve it.
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# 3. Route Everything Else to index.php
# This handles React routes (clean URLs) and Proxy requests.
RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]
```

**2. Download `index.php`**

Download [index.php](https://raw.githubusercontent.com/MehdiMst00/pasarguard-subscription-template/refs/heads/main/deploy/index.php)

Then update the PasarGuard subscription domain:

```php
// Change this
$host_domain = "sub.replace-with-your-pasarguard-domain.com";
```

**3. Download React `index.html`**

Download the React User Dashboard UI:
[index.html](https://raw.githubusercontent.com/MehdiMst00/pasarguard-subscription-template/refs/heads/main/deploy/index.html) 

Place it in the same directory as index.php.

## 📁 Folder Structure

```
/
├── .htaccess
├── index.php # PHP proxy entry point
├── index.html # React user dashboard
```

---

## 🔒 Security Note
This setup hides your main PasarGuard subscription domain behind a proxy,
reducing direct exposure of your backend endpoint.

## ✅ Requirements
- PHP 8.0+
- Apache