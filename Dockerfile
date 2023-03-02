#syntax=docker/dockerfile:1.5

FROM php:8.2.3 AS php-version
FROM composer:2 AS composer-version
FROM golang:1.20 AS golang-version

FROM php-version AS generator-php
WORKDIR /build
RUN apt update && apt install -y protobuf-compiler protobuf-compiler-grpc
RUN --mount=src=./service.proto,dst=./service.proto \
    protoc service.proto --php_out=. --grpc_out=. --plugin=protoc-gen-grpc=/usr/bin/grpc_php_plugin

FROM golang-version AS generator-golang
WORKDIR /build
RUN apt update && apt install -y protobuf-compiler
RUN go install google.golang.org/protobuf/cmd/protoc-gen-go@v1.28
RUN go install google.golang.org/grpc/cmd/protoc-gen-go-grpc@v1.2
RUN --mount=src=./service.proto,dst=./service.proto \
    protoc service.proto --go_out=. --go_opt=paths=source_relative --go-grpc_out=. --go-grpc_opt=paths=source_relative

FROM php-version AS composer-install
RUN apt update && apt install -y git zip
WORKDIR /composer
COPY --link composer.* .
COPY --from=composer-version /usr/bin/composer /usr/bin/composer
RUN composer install

FROM php-version AS ext-protobuf-install
ENV MAKEFLAGS=j4
RUN pecl install protobuf

FROM php-version AS ext-grpc-install
RUN apt update && apt install -y zlib1g-dev
ENV MAKEFLAGS=j4
RUN pecl install grpc

FROM golang-version AS golang-compiler
WORKDIR /build
COPY --link server.go .
COPY --link --from=generator-golang /build service
COPY --link go.* .
RUN go build -o server

FROM php-version AS final
WORKDIR /app
RUN apt update && apt install -y zlib1g
COPY --from=ext-protobuf-install /usr/local /usr/local
COPY --from=ext-grpc-install /usr/local /usr/local
COPY --link <<EOT /usr/local/etc/php/conf.d/ext-protobuf.ini
extension=protobuf.so
extension=grpc.so
EOT
COPY --link --from=composer-install /composer/vendor /app/vendor
COPY --link --from=generator-php /build src-generated
COPY --link src src
COPY --link client.php .
COPY --link --from=golang-compiler /build/server .
CMD ["php", "client.php"]

FROM scratch AS out-generated-php
#todo filter out the metadata stuff?
COPY --link --from=generator-php /build /

FROM scratch AS out-generated-golang
COPY --link --from=generator-golang /build /

FROM scratch AS out-composer-install
COPY --link --from=composer-install /composer/vendor /


FROM final
