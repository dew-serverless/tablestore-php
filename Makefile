# Usage:
# make          - compile Protobuf to PHP classes
# make clean    - clean up the auto-generated PHP classes

.PHONY: all build clean

PROTOC ?= protoc

all: clean build

build:
	${PROTOC} --php_out=src -Iprotos/tablestore protos/tablestore/*.proto

clean:
	rm -rf src/Protos/
