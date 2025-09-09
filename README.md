# EnergexAI Full-Stack Screening Assessment Solution:

## Installation and setup

1. Clone the repository:
    ```bash
    git clone https://github.com/yourusername/energex-assessment.git
    cd energex-assessment
    ```
2. Start the stack with Docker:
   ```bash
   docker compose up -d --build
   ```
3. Start frontend:
    ```bash
   http://localhost:5173
   ```
4. Lumen API:
   ```bash
   http://localhost:8000
   ```
5. MySQL:
   ```bash
   localhost:3308 (user: app, pass: app, db: energex)
   ```
6. Redis:
   ```bash
   localhost:6379
   ```
   
### Environment variables:
All required variables are preconfigured in docker-compose.yml


### Database schema:
Users:

| **id**    | name    | email   | password  |
| :--   | :--      | :--     | :--      |

Posts:

| **id**    | title   | content   | user_id   | created_at  |
| :--       | :--      | :--     | :--      | :--           |


### Authentication flow:
1. Register -> User signs up with name, email, password.
2. Login -> Returns a JWT token.
3. Authorized requests -> Token is passed in the Authorization: Bearer <token> header.
   

### API Endpoints:
Auth

| Method       | Endpoint       |  Description   |
| :--          | :--            | :--            |
| **POST**     | /api/register  | Register a new user      |
| **POST**     | /api/login     | Login and get JWT token  |


Posts (Lumen API)
| Method           | Endpoint         | Auth?      | Description             |
| :--              | :--              | :--        | :--                      |
| **GET**          | /api/posts       |    ❌     | Register a new user      |
| **GET**          | /api/posts/{id}  |    ❌     | Login and get JWT token  |
| **POST**         | /api/posts       |    ✅     |  Description             |
| **PUT**          | /api/posts/{id}  |    ✅     | Register a new user      |
| **DELETE**       | /api/posts/{id}  |    ✅     | Login and get JWT token  |


Cache Layer (Node.js):
| Method       | Endpoint             |  Description             |
| :--          | :--                  | :--                      |
| **GET**      | /cache/posts         | Register a new user      |
| **GET**      | /cache/posts/{id}    | Login and get JWT token  |

Testing:
docker compose exec node-cache npm test


