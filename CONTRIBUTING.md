# 🤝 Contributing to ERPH System

Thanks for your interest in improving **ERPH System**!  
This guide keeps contributions clear, friendly, and consistent.

---

## 🧭 How to Contribute

### 1️⃣ Report bugs 🐛
Open an issue and include:
- What you expected
- What actually happened
- Steps to reproduce
- PHP / MySQL / browser versions (if relevant)
- Screenshots or error messages

### 2️⃣ Suggest features 💡
Describe:
- The problem your idea solves
- Who benefits (admin / teacher)
- A simple example of the workflow

### 3️⃣ Submit code 🛠️
1. Fork the repository
2. Create a branch:
   ```bash
   git checkout -b feature/short-name
   ```
3. Make focused changes (one feature or fix per PR)
4. Test locally with XAMPP / MySQL
5. Commit with a clear message:
   ```bash
   git commit -m "Add teacher report filter by date"
   ```
6. Push and open a Pull Request

---

## ✅ Pull Request Checklist

- [ ] Code follows existing PHP / CSS / JS style in this repo
- [ ] No secrets (`.env`, passwords, local DB credentials) are committed
- [ ] UI text is in **English**
- [ ] SQL uses prepared statements for user input
- [ ] Output is escaped where needed (`htmlspecialchars`)
- [ ] Related docs / README updated if needed
- [ ] Feature was tested as admin and/or teacher

---

## 🧱 Coding Guidelines

### PHP
- Prefer existing helpers in `public/inc/` (`bootstrap.php`, gates, translations)
- Keep page logic readable; avoid large duplicated blocks
- Use PDO prepared statements for database queries

### Frontend
- Reuse styles from `public/assets/css/` when possible
- Keep the blue / glass design language consistent
- Prefer clear labels and accessible contrast

### Database
- Document schema changes with a `.sql` migration file
- Do not drop production data in seed scripts without a clear warning

---

## 🧪 Local Test Tips

```bash
# Import fresh schema
mysql -u root < sql/erph1_fresh.sql

# Optional demo data
mysql -u root < seed_basic_data.sql
```

Open:

```text
http://localhost/erph%20system/public/login_roles.php
```

Default demo accounts are listed in `README.md`.

---

## 🗣️ Communication

- Be respectful and constructive in issues and PRs
- Prefer small PRs that are easy to review
- Link related issues when possible

---

## 📜 License

By contributing, you agree that your contributions will be licensed under the same **MIT License** as this project.

💙 Thank you for helping ERPH System grow!
