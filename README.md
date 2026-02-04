# Clutchify.gg

A custom-built PHP web application for managing Counter-Strike 2 tournaments. This platform handles team registration, automatic match generation, live score tracking via MatchZy webhooks, and player statistics.

## 🚀 Features

- **User Management**: Steam OpenID authentication.
- **Tournament System**:
  - Team creation and management.
  - Automated match scheduling.
  - Map veto system (Ban/Pick phase).
  - Real-time match status (Live/Waiting/Finished).
- **Live Data Integration**:
  - Integrates with CS2 servers using **MatchZy**.
  - Real-time updates for scores, current map, and round history.
  - Automatic stats tracking (Kills, Deaths, Assists, HS%, MVPs).
- **Media**: Twitch stream embedding and news posts.

## 🛠️ Tech Stack

- **Backend**: Native PHP (8.0+)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, Vanilla CSS, JavaScript
- **Dependencies**: managed via Composer
  - `google/protobuf`
  - `textalk/websocket`
  - `thedudeguy/rcon` (Valve Source RCON)

## ⚙️ Installation

### Prerequisites
- PHP 8.0 or higher
- Composer
- MySQL Database
- A CS2 Server with [MatchZy](https://github.com/shobhit-pathak/MatchZy) installed.

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd clutchify
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Configuration**
   - Import the schema from `zsnchampions.sql` into your database.
   - Update database credentials in `src/connect_db.php`:
     ```php
     $host = '127.0.0.1:3306';
     $dbname = 'tournament_app_template';
     $user = 'root';
     $password = '';
     ```

4. **Server Configuration**
   - Check `src/matchzy_events.php` to configure webhook handling.
   - **Note**: Currently, the CS2 server IP for new games is configured in `src/matchzy_events.php`. Ensure this matches your game server.

## 📁 Project Structure

- `src/`: Core logic and helper scripts.
  - `matchzy_events.php`: Webhook handler for game events (round end, map result, etc.).
  - `Config.php`: Main configuration class.
  - `connect_steam.php`: Steam Auth handling.
- `index.php`: Main dashboard / landing page.
- `zsnchampions.sql`: Database schema export.

## ⚠️ Known Limitations

- **Concurrency**: The current `series_end` logic in `matchzy_events.php` (around line 350) executes a truncation of active game tables. This implies the system currently creates a clean state after every series, effectively supporting **only one active match series at a time**.
