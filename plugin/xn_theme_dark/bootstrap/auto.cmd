#npm install node-sass -g
node-sass --output-style expanded --source-map true --source-map-contents true --precision 6 scss/bootstrap.scss ../css/bootstrap.css && node-sass --output-style expanded --precision 6 scss/bootstrap-bbs.scss ../css/bootstrap-bbs.css && node-sass --output-style expanded --precision 6 scss/bootstrap-umeditor.scss ../css/bootstrap-umeditor.css

pause