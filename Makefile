SHELL := bash
.SHELLFLAGS := -eu -o pipefail -c
.ONESHELL:
.SILENT:
.PHONY: *

guid = $(shell id -u):$(shell id -g)

define composer
	docker run --tty --workdir "/app" --volume "./:/app" --user=$(guid) --dns=8.8.8.8 --dns=8.8.4.4 composer sh -c "composer $(1)"
endef

install:
	$(call composer, install)

test:
	$(call composer, test)
