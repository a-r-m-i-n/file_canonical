ARG BASE_IMAGE
FROM $BASE_IMAGE

ENV EXTENSION_KEY "file_canonical"
ENV DDEV_SITENAME "file-canonical"

RUN echo "<html><head><style>h2 {display:inline-block;margin-right:0.5em;vertical-align:middle;}</style></head>" > /var/www/html/index.html
RUN echo "<body><h1>EXT:${EXTENSION_KEY} Dev Environments</h1><ul>" >> /var/www/html/index.html
RUN echo "<li><h2>TYPO3 10.4 LTS</h2><a href="https://v10.${DDEV_SITENAME}.ddev.site/">Frontend</a> | <a href="https://v10.${DDEV_SITENAME}.ddev.site/typo3/">Backend</a></li>" >> /var/www/html/index.html
RUN echo "<li><h2>TYPO3 11.x</h2><a href="https://v11.${DDEV_SITENAME}.ddev.site/">Frontend</a> | <a href="https://v11.${DDEV_SITENAME}.ddev.site/typo3/">Backend</a></li>" >> /var/www/html/index.html
RUN echo "</ul>" >> /var/www/html/index.html
RUN echo "<hr>" >> /var/www/html/index.html
RUN echo "<h3>TYPO3 Backend</h3><ul><li><b>User:</b> <code>admin</code></li><li><b>Password:</b> <code>password</code> (also Install Tool)</li></ul>" >> /var/www/html/index.html
RUN echo "</body></html>" >> /var/www/html/index.html


RUN mkdir -p /var/www/html/v10/public/typo3
RUN echo "<h1>Perform this first</h1> <code>ddev install-v10</code>" > /var/www/html/v10/public/index.html
RUN echo "<h1>Perform this first</h1> <code>ddev install-v10</code>" > /var/www/html/v10/public/typo3/index.html
RUN mkdir -p /var/www/html/v11/public/typo3
RUN echo "<h1>Perform this first</h1> <code>ddev install-v11</code>" > /var/www/html/v11/public/index.html
RUN echo "<h1>Perform this first</h1> <code>ddev install-v11</code>" > /var/www/html/v11/public/typo3/index.html

ARG uid
ARG gid
RUN chown -R $uid:$gid /var/www/html
