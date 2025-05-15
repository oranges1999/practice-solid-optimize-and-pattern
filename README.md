<h3 align="center">
#Practicing Project
</h3>

## Purpose of this project

This project is a playground for me to practice any skills I've learned.

## Knowledge Applied

- **Repository Design Pattern**: This pattern is used to separate the data access logic from the business logic. It provides a clean abstraction for accessing data sources, making the code more maintainable and testable.
- **Service Design Pattern**: This pattern organizes business logic into services, promoting separation of concerns. It helps keep the codebase modular, reusable, and easier to maintain by encapsulating related operations into distinct service classes.
- **SOLID Principles**: A set of five design principles (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, and Dependency Inversion) that help create maintainable, flexible, and scalable software architecture. Applying these principles improves code readability, reduces bugs, and facilitates easier testing and extension.

## Tool Used

- **Laravel**
- **Vue**
- **Docker**
- <del>**MeiliSearch**</del> (removed)
- **Minio**
- **Mailpit**
- **PhpMyAdmin**
- **PhpSpreadSheet**
- **Reverb**
- **Echo**

## How to install the project
Step 1: Clone the project
```bash
git clone https://github.com/oranges1999/practice-solid-optimize-and-pattern.git
```

Step 2: CD into project and copy .env file
```bash
cd /path/to/file/practice-solid-optimize-and-pattern
cp .env.example .env
```

Step 3: Install Sail and setup vendor via Docker
```bash
php artisan sail:install
vendor/bin/sail composer install --ignore-platform-reqs
```

Step 4: Install Node modules
```bash
vendor/bin/sail npm install
```

Step 5: Setup other requirement
```bash
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate:fresh --seed
vendor/bin/sail artisan storage:link
```

## How to run the project
Step 1: Run Sail in the background
```bash
vendor/bin/sail up -d
```

Step 2: Render app.js, app.css
```bash
vendor/bin/sail npm run dev
```

Step 3: Run require service
```bash
sail artisan queue:work
sail artisan schedule:word
sail artisan reverb:start
```