# Learning Hub

Learning Hub is a PHP and MySQL classroom system with separate administrator, teacher, and student accounts. Teachers create classes, lectures, and assignments; students access their assigned class and submit work.

## Requirements

- PHP 8+
- MySQL / MariaDB
- Apache through XAMPP, or the PHP development server
- A database named `learnhub` with the project's existing tables

Update the database connection in `dbConnect.php` if your local MySQL username, password, or database name differs.

## Run locally

From this project directory:

```powershell
php -S localhost:8000
```

Then open <http://localhost:8000>.

## One-time admin setup

Admin credentials are read from environment variables instead of being stored as a readable password in source code.

1. Choose a strong admin password.
2. Generate its password hash:

```powershell
php -r "echo password_hash('YourStrongPassword123!', PASSWORD_DEFAULT), PHP_EOL;"
```

3. Copy the generated hash and set the two permanent Windows environment variables:

```powershell
setx LEARNHUB_ADMIN_USERNAME "admin"
setx LEARNHUB_ADMIN_PASSWORD_HASH "PASTE_THE_GENERATED_HASH_HERE"
```

4. Close and reopen PowerShell, then restart the PHP server or Apache.

Log in using the username and readable password you chose. Do not enter the hash in the login form.

To change the admin password later, generate a new hash and run the second `setx` command again.

## First-use flow

1. Log in as administrator and create teacher and student accounts.
2. Log in as a teacher and create a class.
3. When creating a student, enter the exact class name created by the teacher.
4. Teacher adds lectures or assignments.
5. Student logs in, opens the assigned class, and submits a permitted document.
6. Teacher opens **Check Student Work** from that class.

## Security features

- Passwords created or changed by the application use `password_hash()`.
- Existing teacher and student readable passwords are upgraded to hashes after the next successful login.
- Role checks protect admin, teacher, and student areas.
- CSRF tokens protect important form submissions and logout.
- Student classroom access is restricted to the student's assigned class.
- Teacher work review is restricted to the selected class.
- Uploaded documents receive random filenames, have a 10 MB limit, and only allow: PDF, DOC, DOCX, PPT, PPTX, TXT, and XLSX.

## Important upload note

The application blocks new PHP uploads. Review any old `.php` files in `uploads/assignment` and `uploads/lecture`; delete them only if they are not required coursework.
