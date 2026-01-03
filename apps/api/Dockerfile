# Use an official Node.js runtime as a parent image
FROM node:20-alpine

# Set the working directory in the container
WORKDIR /usr/src/app

# Copy package.json and yarn.lock to leverage Docker cache
COPY package.json yarn.lock ./

# Install app dependencies
RUN yarn install

# Copy the rest of the application's source code
COPY . .

# Make port 4000 available to the world outside this container
EXPOSE 4000

# Run the app when the container launches
CMD ["yarn", "start"]
