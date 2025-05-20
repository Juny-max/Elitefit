# EliteFit

EliteFit is a comprehensive gym and fitness management platform designed for members, trainers, admins, and equipment managers. It features workout tracking, AI-powered chat assistance, trainer-member communication, progress tracking, and robust admin tools.

---

## Features

- **Member Dashboard:**
  - Interactive workout calendar and progress tracking
  - Log workouts (including fractional durations)
  - View completed workouts by category
  - AI Gym Assistant (animated chat widget)
  - Messaging system

- **Trainer Portal:**
  - Assign and manage member workouts
  - Track member progress and availability
  - Communicate with members

- **Admin Tools:**
  - Dashboard for managing members, trainers, and equipment
  - Archive and settings management

- **AI Chat Widget:**
  - Animated, gradient-glow AI assistant for gym/fitness help
  - Accessible from member dashboard

- **Database:**
  - MySQL schema with users, trainers, equipment, workouts, progress, goals, and more
  - Migration scripts for schema updates

---

## Project Structure

```
/elitefit
│
├── admin/               # Admin dashboard and management tools
├── member/              # Member dashboard, AI chat, workout actions
│   ├── actions/         # Member AJAX/API endpoints
│   ├── ai_chat.php      # AI chat backend
│   ├── ai_chat_widget.js# AI chat widget frontend
│   ├── dashboard.php    # Member dashboard
│   └── messages.php     # Messaging
├── trainer/             # Trainer dashboard and tools
├── migrations/          # SQL migration scripts
├── equipment/           # Equipment management
├── password-reset/      # Password reset flows
├── uploads/             # File uploads
├── vendor/              # Composer dependencies
├── elitefit.sql         # Main SQL schema
├── config.php           # Database/config
├── index.php, index.html# Landing
├── register.php         # Registration
├── login_process.php    # Login
├── ...
```

---

## Setup & Installation

1. **Clone the repository** and place it in your web server directory (e.g. `wamp64/www/elitefit`).
2. **Install dependencies** (if needed):
   - PHP 7.4+
   - MySQL
   - Composer (`composer install` in the root directory)
3. **Database Setup:**
   - Import `elitefit.sql` into your MySQL server.
   - Apply any scripts in `migrations/` for schema updates.
   - Edit `config.php` with your database credentials.
4. **Run the application:**
   - Open `http://localhost/elitefit` in your browser.

---

## Customization

- **AI Chat Widget:**
  - Edit `member/ai_chat_widget.js` for animation/appearance tweaks.
  - Backend handled in `member/ai_chat.php`.
- **Add new workout categories or plans:**
  - Update via SQL or admin dashboard.
- **Styling:**
  - Update CSS in relevant PHP/HTML/JS files.

---

## Credits

Developed by the Juny.

---

## License

This project is for educational and demonstration purposes. Contact the author for commercial use.
