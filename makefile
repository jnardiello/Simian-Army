BASE_DIR=/var/www

update: 
	rsync -avzh ./web/ $(BASE_DIR)/simian.army/web/
