Changelog
=========

3.0.0
-----

* Support Symfony 6 & 7
* Drop support for old Symfony versions
* Supprt PHP 8.1 - 8.3
* Drop support for old PHP versions
* TranslatableInterface now uses typehints, adjust your implementations accordingly.
* Use DateTimeInterface instead of DateTime.
* Adjust to doctrine and twig BC breaks. If you extended classes or customized services, check for old `Twig_*` classes or `Doctrine\Common\Persistence` namespace.

2.1.1
-----

* Fixed another template reference to work with Symfony 4.

2.1.0
-----

* Symfony 4 support
* Removed PHP 5.6 and 7.0 support, removed support for Symfony 3.0 - 3.2

2.0.0
-----

Released 2.0.0

2.0.0-RC3
---------

 * **2017-02-10**: `content_basepath` is now prepended on the CmfSeoBundle.
 * **2017-02-10**: `content_basepath` is no longer prepended on the
   CmfRoutingBundle, as the setting is removed.

2.0.0-RC2
---------

 * **2017-01-29**: Persistence configuration is no longer prepended to the
   CmfTreeBrowserBundle as these options have been removed.
 * **2017-01-29**: Fixed security voters to allow non-object subjects.

2.0.0-RC1
---------

 * **2017-01-17**: [BC BREAK] Removed DoctrineOrmMappingsPass - all active Doctrine versions contain the mapping pass. Use `Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass` instead.
 * **2017-01-13**: [BC BREAK] Removed the `Slugifier` classes and the
   dependency on `symfony-cmf/slugifier-api`.
 * **2016-12-03**: [BC BREAK] Moved sonata admin related classes and
   configuration to the CmfSonataAdminIntegrationBundle.
 * **2016-06-19**: [BC BREAK] Removed second and third argument from `CmfHelper` constructor.
 * **2016-06-19**: [BC BREAK] Removed `RequestAwarePass` class.
 * **2016-06-19**: [BC BREAK] Removed `TranslatableExtension` class and related
   `cmf_core.admin_extension.translatable` service.
 * **2016-06-18**: [BC BREAK] Removed all `*.class` parameters.
 * **2016-04-30**: [BC BREAK] Dropped PHP <5.5 support.
 * **2016-04-30**: [BC BREAK] Dropped Symfony <2.8 support.

1.3.0
-----

Released 1.3.0

1.3.0-RC1
---------

* **2016-02-01**: The class and interface in the `Slugifier` namespace are deprecated in favor of the `symfony-cmf/slugifier-api` package.
* **2016-01-24**: The `TranslatableExtension` and `cmf_core.admin_extension.translatable` services are deprecated in favor of the SonataTranslationBundle
* **2015-04-12**: [BC BREAK] The following services were made private: `cmf_core.admin_extension.child`, `cmf_core.security.publishable_voter`, `cmf_core.security.publish_time_period_voter`, `cmf_core.security.published_voter`, `cmf_core.admin_extension.publish_workflow.publishable`, `cmf_core.admin_extension.publish_workflow.time_period`, `cmf_core.twig.children_extension`, `cmf_core.templating.helper`, `cmf_core.persistence.phpcr.non_translatable_metadata_listener`, `cmf_core.persistence.phpcr.translatable_metadata_listener`, `cmf_core.admin_extension.translatable`
* **2015-04-12**: The following services could not be private, but should be considered as such: `cmf_core.publish_workflow.request_listener`, `cmf_core.form.type.checkbox_url_label`

1.2.0
-----

1.2.0-RC1
---------

* **2014-06-06**: Updated to PSR-4 autoloading

1.1.0
-----

* **2014-05-08** [Multilang]: When using phpcr-odm but not configuring
  cmf_core.multilang.locales, the metadata listener now makes all documents
  non-translated. It no longer checks whether the document implements
  `TranslatableInterface`.

1.1.0-RC2
---------

* **2014-04-11**: drop Symfony 2.2 compatibility, also the "cmf_request_aware" tag
    has been deprecated. please add the ``setRequest()`` call manually now:
  ``<call method="setRequest"><argument type="service" id="request" on-invalid="null" strict="false"/></call>``

1.1.0-RC1
---------

* **2014-02-14**: Twig function cmf_linkable_children now uses cmf_linkable
  which considers documents to be linkable if they are either route, or have
  actually a route pointing to them. (Previously, just having the interface
  for route referrers was enough, even if there was no route.)

1.0.0-RC7
---------

* **2013-10-03**: added support for setting a global PHPCR ODM translation strategy

1.0.0-RC5
---------

* **2013-09-04**: added prepend support for CmfSearchBundle and CmfTreeBrowserBundle
  and various tweaks to the existing prepend support

1.0.0-RC2
---------

* **2013-08-04**: [Doctrine ORM] Fix doctrine orm compiler pass to match
  signature of the one in the doctrine bridge.

* **2013-08-01**: [PublishWorkflow] Adjusted interfaces to naming conventions.
  PublishableInterface is now read and write, PublishableReadInterface for read
  only. PublishTimePeriod is adjusted the same way.

1.0.0-RC1
---------

* **2013-07-29**: [DependencyInjection] Implemented PrependExtensionInterface
* **2013-07-29**: [DependencyInjection] Renamed config item `document_manager_name` to `persistence.phpcr.manager_name`

* **2013-07-26**: The CoreBundle now supports translatable models. For
  phpcr-odm you need to configure the locales or a metadata listener will
  convert the properties to not translated.

* **2013-06-20**: [PublishWorkflow] The PublishWorkflowChecker now implements
  SecurityContextInterface and the individual checks are moved to voters.
  Use the service cmf_core.publish_workflow.checker and call
  `isGranted('VIEW', $content)` - or `'VIEW_ANONYMOUS'` if you don't want to
  see unpublished content even if the current user is allowed to see it.
  Configuration was adjusted: The parameter for the role that may see unpublished
  content moved from `role` to `publish_workflow.view_non_published_role`.
  The security context is also triggered by a core security voter, so that
  using the isGranted method of the standard security will check for
  publication.
  The PublishWorkflowInterface is split into the reading interfaces
  PublishableReadInterface and PublishTimePeriodReadInterface as well as
  PublishableInterface and PublishableTimePeriodInterface. The sonata
  admin extension has been split accordingly and there are now
  cmf_core.admin_extension.publish_workflow.time_period and
  cmf_core.admin_extension.publish_workflow.publishable.

* **2013-05-16**: [PublishWorkFlowChecker] Removed Request argument
  from check method. Class now accepts a DateTime object to
  optionally "set" the current time.
