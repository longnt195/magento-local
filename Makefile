build :
	@docker-compose build --no-cache $(service)
# 	@docker-compose build $(service)
up:
	@echo "Docker Up"
	@docker-compose up -d
up_newrelic:
	@echo "New Relic Docker Up"
	@docker-compose -f docker-compose.newrelic.yml up -d
stop:
	@echo "Docker Stop"
	@docker-compose stop
restart:
	@echo "Docker Stop"
	@docker-compose restart
snd:
	@echo "Docker Stop and Down (stop and remove all container data - becaful)"
	@docker-compose down
go:
	@docker exec --user www-data -it php81 /bin/bash
godb:
	docker exec -it mariadb /bin/bash
gonginx:
	docker exec -it nginx /bin/bash
goos:
	docker exec -it opensearch12 /bin/bash
go81:
	docker exec -it php81 /bin/bash
go_newrelic:
	docker exec -it newrelic-infra /bin/bash
go_npd:
	docker exec -it newrelic-php-daemon /bin/bash

chmod:
	@echo "Update permission"
	@sudo chmod -R 777 ./magento/src/var ./magento/src/generated ./magento/src/pub/static ./magento/src/pub/media #find . -type f -exec chmod -c 644 {} \; && sudo find . -type d -exec chmod -c 755 {} \;

upg:
	@echo "Upgrade"
	#php bin/magento setup:upgrade
	docker exec $(container_php) /usr/local/bin/php -d memory_limit=-1 /var/www/html/src/bin/magento setup:upgrade

dp: cf
	@echo "Deploy"
	docker exec $(container_php) rm -rf /var/www/html/src/pub/static/frontend/*
	docker exec $(container_php) rm -rf /var/www/html/src/pub/static/adminhtml/*
	docker exec $(container_php) rm -rf /var/www/html/src/var/view_preprocessed/*
	docker exec $(container_php) rm -rf /var/www/html/src/var/page_cache/*
	docker exec $(container_php) rm -rf /var/www/html/src/generated/*
	docker exec $(container_php) /usr/local/bin/php -d memory_limit=-1 /var/www/html/src/bin/magento setup:static-content:deploy -f
ri:
	@echo "Reindex"
	#php bin/magento indexer:reindex
	docker exec $(container_php) /usr/local/bin/php -d memory_limit=-1 /var/www/html/src/bin/magento indexer:reindex
cf:
	@echo "Flush cache"
	#php bin/magento cache:flush
	docker exec $(container_php) /usr/local/bin/php -d memory_limit=-1 /var/www/html/src/bin/magento c:f
di:
	@echo "Compile"
	#php bin/magento setup:di:compile
	docker exec $(container_php) /usr/local/bin/php -d memory_limit=-1 /var/www/html/src/bin/magento setup:di:compile
rm:
	@echo "Remove cache files"
	rm -rf magento/src/pub/static/frontend/*
	rm -rf magento/src/pub/static/adminhtml/*
	rm -rf magento/src/var/view_preprocessed/*
	rm -rf magento/src/var/page_cache/*
	rm -rf magento/src/var/cache/*
	rm -rf magento/src/generated/*
all: upg ri dp cf

user_connect:
	docker exec -it $(container_services) ssh -4 -D 9080 -o ProxyCommand='ssh -W %h:%p -i sixpad-dev-debug.pem -p 22 ec2-user@54.249.151.117' -i sixpad-dev-ec-ec2.pem -p 22 ubuntu@$(dev_ip)

reset_css:
	sudo rm -rf krispykreme-m2/var/view_preprocessed/*
	sudo rm krispykreme-m2/pub/static/frontend/KrispyKremeFundraising/default/en_AU/css/styles-l.css
	sudo rm krispykreme-m2/pub/static/frontend/KrispyKremeFundraising/default/en_AU/css/styles-m.css