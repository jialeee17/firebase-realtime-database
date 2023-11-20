## About Hap2py Firebase

Welcome to Hap2py Firebase, a robust Laravel project designed to simplify the integration of Firebase services into your applications. This standalone project is dedicated to providing seamless APIs that empower your projects to leverage the full potential of Firebase features, including Realtime Database and more.

### Key Features
#### Realtime Database APIs

**Store Data**: Seamlessly store data in real-time with the Firebase Realtime Database API provided by Hap2py Firebase.

## Project Setup

Follow these steps to set up and run the project locally.

### Prerequisites

List any dependencies or prerequisites that need to be installed before running the project.

- PHP 8.1.x or 8.2.x
- A Firebase project - create a new project in the [Firebase console](https://console.firebase.google.com/u/0/), if you don’t already have one.

### Installation

1. Clone the repository to your local machine.

   ```bash
   git clone https://gitlab.com/hp-devs/hp-firebase.git
   ```

2. Copy & Paste the **.env.example**, and rename it to **.env**.
3. Store the firebase credentials inside **storage/app**.
4. Update the firebase credentials path.
    ```
    FIREBASE_CREDENTIALS=storage/app/firebase-auth.json
    ```
4. Update the Database URL, You can find the database URL for your project at [here](https://console.firebase.google.com/project/_/databaseif).
    ```
    FIREBASE_CREDENTIALS=storage/app/firebase-auth.json
    ```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).