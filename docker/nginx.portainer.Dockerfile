FROM nginx:alpine

COPY nginx/conf.d /etc/nginx/conf.d
COPY nginx/ssl /etc/nginx/ssl
COPY public /var/www/public