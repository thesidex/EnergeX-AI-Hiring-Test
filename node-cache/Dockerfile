FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY tsconfig.json ./
COPY src ./src
ENV NODE_ENV=production
RUN npm run build
EXPOSE 4000
CMD ["node", "dist/index.js"]
