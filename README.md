# Turbo Monorepo Project: API, Web App, and Admin Portal
This is a monorepo project managed by Turborepo, containing three main components:

- apps/api: A Node.js and Express.js backend server with a placeholder for CRUD APIs, connected to a PostgreSQL database.
- apps/web: A Next.js end-user web application.
- apps/admin: A React (Vite) admin portal using Material-UI and Zustand for state management.

The entire local development environment is orchestrated with Docker, and deployment is automated via a GitHub Actions CI/CD pipeline to a Kubernetes cluster on AWS (EKS).

## Prerequisites
Before you begin, ensure you have the following tools installed on your local machine:
- Node.js (v20.x or later)
- npm (v10.x or later)
- Docker and Docker Compose
- AWS CLI (for deployment)
- kubectl (for interacting with the Kubernetes cluster)

## 🚀 Running the Project Locally
Follow these steps to get all services up and running on your local machine.

**1. Clone the Repository**

```bash
git clone <your-repository-url>
cd <your-repository-name>
```

**2. Install Dependencies**

Install all the dependencies for the monorepo from the root directory.
```bash
npm install
```

**3. Start All Services with Docker**

The `docker-compose.yml` file is configured to build and run the backend API, the user-facing web app, the admin portal, and a PostgreSQL database.

```
npm run docker:dev
```

This command will:
- Build the Docker images for each application if they don't exist.
- Start a container for each service.
- Enable hot-reloading for the frontend and backend applications.


**4. Access the Applications**

Once the containers are running, you can access the services at the following URLs:

- End-User Web App (Next.js): http://localhost:3000
- Admin Portal (React): http://localhost:3001
- Backend API Server: http://localhost:4000
- PostgreSQL Database: Accessible on port 5432

**5. Stop the Services**

To stop all running containers, run the following command from the root directory:

```bash
npm run docker:stop
```

## ☁️ Deploying to AWS EKS with GitHub Actions
The CI/CD pipeline is configured to automatically build and deploy the applications to an AWS EKS cluster when code is pushed to the main branch.

**1. AWS Prerequisites**

Ensure you have the following resources set up in your AWS account:
- An Amazon EKS Cluster.
- An Amazon ECR (Elastic Container Registry) repository to store your Docker images.
- An Ingress Controller (e.g., AWS Load Balancer Controller) installed in your EKS cluster to manage external access to your services.
- A configured domain name to point to your applications.


**2. Configure GitHub Actions Secrets and Variables**

In your GitHub repository, go to `Settings > Secrets and variables > Actions` and configure the following:

Secrets:
- AWS_ACCESS_KEY_ID: Your AWS access key ID.
- AWS_SECRET_ACCESS_KEY: Your AWS secret access key.
- AWS_REGION: The AWS region where your EKS cluster and ECR registry are located (e.g., us-east-1).

Variables:
- ECR_REGISTRY: The full URL of your Amazon ECR registry (e.g., 123456789012.dkr.ecr.us-east-1.amazonaws.com).
- EKS_CLUSTER_NAME: The name of your EKS cluster.


**3. Create Kubernetes Secrets**

The API server requires a database connection string to connect to a production database. You should create a Kubernetes secret to store this securely.

Connect your `kubectl` to your EKS cluster and run the following command, replacing the placeholder value with your actual production database URL:
```
kubectl create secret generic api-secrets \
  --from-literal=database-url='postgresql://user:password@host:port/dbname'
```

**4. Update the Ingress Manifest**

Before deploying, you need to update the Ingress resource file (k8s/ingress.yml) with your domain names.

Open `k8s/ingress.yml` and replace the placeholder hosts:
- my-app.yourdomain.com
- admin.my-app.yourdomain.com
- api.my-app.yourdomain.com

Make sure the annotations in the Ingress manifest match your Ingress Controller (e.g., for the AWS Load Balancer Controller).

**5. Trigger the Deployment**

Commit and push your changes to the `main` branch.
```
git push origin main
```
This will trigger the GitHub Actions workflow defined in `.github/workflows/deploy.yml`. The workflow will:

- Build a Docker image for each application.
- Push the images to your Amazon ECR registry.
- Update the Kubernetes deployment manifests with the new image tags.
- Apply the manifests to your EKS cluster, deploying the latest version of your applications.