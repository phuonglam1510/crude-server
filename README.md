# 99Tech Code Challenge 

## Problem 5: A Crude Server

**Task Requirements**

Develop a backend server with ExpressJS. You are required to build a set of CRUD interface that allow a user to interact with the service. You are required to use TypeScript for this task.

1. Interface functionalities:
    1. Create a resource.
    2. List resources with basic filters.
    3. Get details of a resource.
    4. Update resource details.
    5. Delete a resource.
2. You should connect your backend service with a simple database for data persistence.
3. Provide [`README.md`](http://README.md) for the configuration and the way to run application.

## Introduction

This is a backend server built with ExpressJS, TypeScript, and TypeORM. It provides a complete CRUD (Create, Read, Update, Delete) interface for managing resources, using PostgreSQL for data persistence. The database is set up and managed effortlessly with Docker, ensuring a consistent development environment.

## Features
**Resource Management**: A full suite of API endpoints to interact with resource data.

**Data Validation**: The server handles data validation to ensure data integrity.

**Filtered Listing**: Retrieve a list of resources with optional filters to narrow down search results.

**Database Integration**: Seamlessly connects to a PostgreSQL database using TypeORM for object-relational mapping.

**Modular Structure**: The code is organized into a modular and scalable structure, making it easy to maintain and extend.

**Dockerized Database**: The PostgreSQL database runs in a Docker container, eliminating manual setup and configuration hassles.

## Tech Stack
**Backend Framework**: ExpressJS

**Language**: TypeScript

**ORM**: TypeORM

**Database**: PostgreSQL

**Containerization**: Docker


## How to Run Locally
Follow these steps to get the application up and running on your local machine.


**1. Prerequisites**

   Make sure you have the following installed:
- Node.js (v14 or higher)
- npm (comes with Node.js)
- Docker and Docker Compose

**2. Clone the Repository**

Clone the project repository from Git and navigate into the project directory.

>git clone  https://github.com/phuonglam1510/crude-server
>
>cd crude-server

**3. Set Up the Database with Docker**

The project uses Docker to manage the PostgreSQL database. The configuration is defined in the docker-compose.yml file.

To start the database container, open your terminal in the project root and run:

>docker-compose up -d

This command will pull the postgres:13 image and start a new container named db in the background. It will be accessible on localhost:5432 with the credentials defined in the docker-compose.yml file.

**4. Install Dependencies**

Install all the required Node.js packages for the server:
>npm install

**5. Run the Application**

You can run the application in two modes:

Development Mode
For development, use ts-node-dev. It automatically restarts the server whenever you make a code change.

>npm run dev

Production Mode
To run the server for a production-like environment, use ts-node.

>npm start

Once the server is running, you will see a message in your terminal indicating that it's listening on http://localhost:3000.




## How to test
You can use following CURL command to test the APIs

**1. Create a New Resource**

This POST request creates a new resource with a name and description.

```bash
curl -X POST http://localhost:3000/api/resources \
-H "Content-Type: application/json" \
-d '{"name": "My First Resource", "description": "This is a test resource."}'
```

**2. Get a List of All Resources**

This GET request retrieves all resources from the database.

```bash
curl -X GET http://localhost:3000/api/resources
```
**3. Get a Resource by ID**

This GET request fetches a single resource by its unique ID. Replace <id> with the ID of a resource you've already created.

```bash
curl -X GET http://localhost:3000/api/resources/<id>
```
**4. Update a Resource by ID**

This PUT request updates an existing resource. Replace <id> with the ID of the resource you want to modify.

```bash
curl -X PUT http://localhost:3000/api/resources/<id> \
-H "Content-Type: application/json" \
-d '{"name": "Updated Resource", "description": "The description has been changed."}'
```
**5. Delete a Resource by ID**

This DELETE request removes a resource from the database. Replace <id> with the ID of the resource you want to delete.

```bash
curl -X DELETE http://localhost:3000/api/resources/<id>
```