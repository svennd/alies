

# master is the name / version 
podman build -f podman/Containerfile -t alies-php:master .

# check content
podman run --rm localhost/alies-php:master ls -la /var/www/html