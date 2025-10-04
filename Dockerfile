FROM dunglas/frankenphp

RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache

COPY . /app

ENV FRANKENPHP_CONFIG="worker ./public/index.php"
