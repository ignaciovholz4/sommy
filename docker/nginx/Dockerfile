FROM nginx:1.27-alpine

RUN apk add --no-cache openssl

COPY docker/nginx/conf.d /etc/nginx/conf.d
COPY docker/nginx/templates /etc/nginx/templates
COPY docker/nginx/entrypoint.sh /docker-entrypoint.d/99-generate-ssl-and-start.sh

RUN chmod +x /docker-entrypoint.d/99-generate-ssl-and-start.sh

# Default value; set in VPS env/compose for real domain
ENV NGINX_SERVER_NAME=localhost

EXPOSE 80 443
