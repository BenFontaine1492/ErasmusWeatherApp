VENV_DIR := venv
PYTHON := python3

install:
	# Node.js setup
	# mkdir -p vendor && cd vendor && npm init -y && npm install chart.js
	# Python setup
	$(PYTHON) -m venv $(VENV_DIR)
	$(VENV_DIR)/bin/pip install --upgrade pip
	$(VENV_DIR)/bin/pip install -r requirements.txt

startup:
	make build_dev
	make install
	make run

build_dev:
	docker-compose build

run:
	docker-compose up -d

rebuild:
	make stop
	make build_dev
	make run

stop:
	docker-compose down

restart: stop run

logs:
	docker-compose logs -f

ps:
	docker-compose ps
