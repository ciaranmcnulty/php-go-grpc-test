.PHONY: *

clean:
	rm -rf service src-generated

generate: clean
	docker buildx bake generate

build:
	docker buildx bake --load

run: build
	docker run --rm grpc-runtime
