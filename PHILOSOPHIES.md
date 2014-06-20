# Philosophies

This document outlines the development philosophies of the WordPoints library(ies).

In addition to this document, it is recommended that you also understand [WordPress's
development philosophies](https://make.wordpress.org/core/handbook/our-philosophies/).

## Backward Compatibility With Other Libraries

Libraries MAY contain code whose sole purpose it to maintain backward compatibility
with older versions of other libraries (e.g., WordPress). However, libraries SHALL
NOT contain such compatibility code for versions of libraries which are no longer
actively maintained with security updates (e.g., WordPress 3.6).

## Escaping

Everything must be escaped immediately before output. Period. [A great read on this
topic](http://vip.wordpress.com/2014/06/20/the-importance-of-escaping-all-the-things/).
