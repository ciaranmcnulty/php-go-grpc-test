group "default" {
    targets = [
      "runtime",
    ]
}

group "generate" {
  targets = [
    "php-generated",
    "golang-generated",
  ]
}

target "php-generated" {
  target = "out-generated-php"
  output = [
    "type=local,dest=src-generated,mode=mirror",
  ]
}

target "golang-generated" {
  target = "out-generated-golang"
  output = [
    "type=local,dest=service",
  ]
}

target "composer" {
  target = "out-composer-install"
  output = [
    "type=local,dest=vendor",
  ]
}

target "go-compiler" {
  target = "golang-compiler"
  tags = ["temp"]
}

target "runtime" {
    target = "final"
    tags = [
      "grpc-runtime"
    ]
}
