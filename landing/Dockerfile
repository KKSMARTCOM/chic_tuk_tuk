# ================================
# Build
# ================================
FROM node:22-alpine AS builder
WORKDIR /app

COPY package.json package-lock.json ./
# --ignore-scripts : le postinstall (`nuxt prepare`) a besoin des sources, qui ne sont
# pas encore copiées à ce stade. `nuxt build` refait cette préparation de toute façon.
RUN npm ci --ignore-scripts

COPY . .
RUN npm run build

# ================================
# Runtime : serveur Nitro autonome
# ================================
FROM node:22-alpine AS production
WORKDIR /app

ENV NODE_ENV=production \
    HOST=0.0.0.0 \
    PORT=3000

# .output est autonome : aucun node_modules n'est nécessaire à l'exécution.
COPY --from=builder /app/.output ./.output

# Les URLs d'API et d'application sont lues à l'exécution (pas au build) :
# la même image sert staging et production.
#   NUXT_PUBLIC_API_BASE, NUXT_PUBLIC_APP_URL

USER node
EXPOSE 3000
CMD ["node", ".output/server/index.mjs"]
