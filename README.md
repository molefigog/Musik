# 🧾 Nid POS - Setup Guide

A simple POS (Point of Sale) system for local development.

---

## 📦 Requirements

Make sure the following software is installed:

-   [XAMPP](https://www.apachefriends.org/download.html)
-   [Composer](https://getcomposer.org/download/)

---

## 🧰 Step-by-Step Setup Instructions

### ✅ 1. Install XAMPP

1. Download XAMPP from [apachefriends.org](https://www.apachefriends.org/download.html).
2. Install it and launch the **XAMPP Control Panel**.
3. Start both **Apache** and **MySQL** services.

---

### ✅ 2. Install Composer

1. Download Composer from [getcomposer.org](https://getcomposer.org/download/).
2. Run the installer and follow the instructions.
3. Verify installation in your terminal:
    ```bash
    composer --version
    ```

---

### 📁 3. Move Project Folder

Move the `Nid-Pos` folder to your `C:` drive:

```
C:\Nid-Pos
```

---

### 🛠️ 4. Configure Apache Virtual Host

1. Open the file:
    ```
    C:\xampp\apache\conf\extra\httpd-vhosts.conf
    ```
2. Add the following at the end of the file:

    ```apache
    <VirtualHost *:80>
        DocumentRoot "C:/Nid-Pos/public"
        ServerName nid-pos.local

        <Directory "C:/Nid-Pos/public">
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

3. Save and close the file.

---

### 🧭 5. Edit the Hosts File

1. Open Notepad **as Administrator**.
2. Open this file:
    ```
    C:\Windows\System32\drivers\etc\hosts
    ```
3. Add this line to the bottom:

    ```
    127.0.0.1 nid-pos.local
    ```

4. Save and close.

---

### 🔁 6. Restart Apache

In the XAMPP Control Panel, **stop and restart Apache** to apply changes.

---

### 📦 7. Install PHP Dependencies

1. Open **Command Prompt** or **Terminal**.
2. Navigate to the project folder:
    ```bash
    cd C:\Nid-Pos
    ```
3. Run Composer install:
    ```bash
    composer install
    ```

---

### ⚙️ 8. Environment Setup

1. Copy the example `.env` file:
    ```bash
    copy .env.example .env
    ```
2. Generate Laravel application key:

    ```bash
    php artisan key:generate
    ```

3. Open `.env` and set your database configuration, e.g.:

    ```dotenv
    DB_DATABASE=nid_pos
    DB_USERNAME=root
    DB_PASSWORD=
    ```

---

### 🗃️ 9. Setup Database

1. Open **phpMyAdmin** from XAMPP or go to:
    ```
    http://localhost/phpmyadmin
    ```
2. Create a database named:
    ```
    nid_pos
    ```
3. (Optional) Run migrations:
    ```bash
    php artisan migrate
    ```

---

### ✅ 10. Access the Application

Open your browser and go to:

```
http://nid-pos.local
```

---

## 🧑‍💻 Support

If you run into issues, double-check that:

-   Apache & MySQL are running
-   VirtualHost & hosts file are correctly set
-   Composer dependencies are installed

---

Happy coding! 🚀
