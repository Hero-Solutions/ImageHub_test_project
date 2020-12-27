#!/bin/bash

php /opt/imagehub/bin/console app:datahub-to-resourcespace > /opt/imagehub/output.txt 2>&1
php /opt/imagehub/bin/console app:generate-iiif-manifests >> /opt/imagehub/output.txt 2>&1
