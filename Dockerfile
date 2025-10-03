FROM dunglas/frankenphp

COPY . /app/public

RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache

ENV FRANKENPHP_CONFIG="worker ./public/index.php"
