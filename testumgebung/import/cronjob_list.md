- import_stocks.php
	- Jede Stunde zur 17. Minute (0:17, 1:17 etc.)
	- Aufrufe
		- updateImportData
		- stocks

- import_desadv.php
	- Jede Stunde zur 37. Minute (0:37, 1:37 etc.)
	- Aufrufe
		- desadv

- import_ups.php
	- Jeden Tag um 17 und 18 Uhr
	- Aufrufe
		- desadv
		- importUPS

- clear_cache.php
	- Jeden Tag um 00:01 Uhr und 00:27 Uhr
	- Aufrufe
		- clear_cache

- cleanup.php
	- Jeden Tag um 5:00 Uhr morgens
	- Aufrufe
		- cleanup

- solrindex.php
	- Jeden Tag um 4:30 Uhr
	- Aufrufe
		- solrindex

- import_product_data.php
	- Jeden Tag um 2:00 Uhr morgens
	- Aufrufe
		- updateImportData
		- colors
		- sizes

- import_artikel.php
	- Jeden Tag um 2:10 Uhr
	- Aufrufe
		- updateImportData
		- products

- import_data.php
	- Jeden Tag um 3:30 Uhr
	- Aufrufe
		- updateImportData
		- stocks
		- kunden
		- stores

- cron_cache_reindex.php
	- Jeden Tag um 4:30 Uhr
	- Aufrufe
		- createShoppingXML
		- reIndex
		- clear_cache

- import_dhl.php
	- Jeden Tag um 17 und 18 Uhr
	- Aufrufe
		- importDHL
