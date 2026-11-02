FROM opensearchproject/opensearch:2.5.0

USER root
RUN yum -y install zip && \
    zip -q -d /usr/share/opensearch/lib/log4j-core-*.jar org/apache/logging/log4j/core/lookup/JndiLookup.class && \
    yum remove -y zip && \
    yum -y clean all && \
    rm -rf /var/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER opensearch

RUN bin/opensearch-plugin install -b analysis-icu && \
    bin/opensearch-plugin install -b analysis-phonetic

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]