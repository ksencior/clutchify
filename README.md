# Clutchify 🎮

**Clutchify** is a lightweight, framework-free web platform designed for managing **Counter-Strike 2 (CS2)** tournaments. Built with native PHP and MySQL, it provides a straightforward solution for organizing esports events, managing teams, and tracking match progress.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

> [!WARNING]
> Be aware, that Clutchify.gg is still in Work In Progress state.

<img src="./assets/img/clutchify-w-text.png" width="256" height="256">

## 🚀 Key Features

- **Automated Web Installer:** Easy setup wizard to configure your database and admin account in seconds.
- **Tournament Management:** Create and manage brackets, teams, and match progressions.
- **Steam & RCON Integration:** Built-in support for Steam API and RCON server commands for match control.
- **User & Team System:** Full player registration, team creation, and roster management.
- **Pure Performance:** No heavy frameworks (React/Vue/Laravel). Just pure, optimized PHP and native JS.
- **Responsive Esports UI:** A modern, dark-themed interface tailored for the CS2 community.

## 🛠️ Tech Stack

- **Backend:** Native PHP (Procedural/OOP)
- **Database:** MySQL
- **Frontend:** Vanilla HTML5, CSS3, and JavaScript
- **Server:** Compatible with Apache/Nginx (XAMPP, WAMP, etc.)

## 📦 Quick Installation

Clutchify features a built-in installer to get you up and running without manual SQL imports.

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ksencior/clutchify.git
   ```
2. **Upload to Server:**
  Move the project files to your web server's root (e.g., `htdocs` or `/var/www/html`).
3. **Run the Installer:**
  Open your browser and navigate to:
    ```bash
    http://your-domain.com/
    ```
4. **Follow the Wizard:**
  - Enter your MySQL Database credentials.
  - Set your App Name and Base URL.
  - Create your Admin Account.
  - The installer will automatically create the database, import tables, and generate your .env file.
5. **Post-Installation:**
  For security, it is recommended to delete or restrict access to install.php once the setup is complete.

## ⚙️ Post-Install Configuration
Once installed, you can further customize your .env file to enable advanced features:\
  **STEAM_API_KEY:** Required for Steam OpenID login.\
  **RCON settings:** Required for automated game server management.

## ⚠️ License & Legal Notice
Copyright (c) 2024-2026 ksencior. All rights reserved.\
<sub>This software is proprietary and remains the intellectual property of the author.
Unauthorized use, reproduction, modification, or distribution of this code, in whole or in part, is strictly prohibited.
You do not have permission to use this software for commercial purposes or host it publicly without explicit written consent from the author.
If you are interested in using Clutchify for a tournament or contributing to the project, please contact the author directly via GitHub.<sub>
