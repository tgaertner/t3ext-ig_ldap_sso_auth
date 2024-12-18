﻿.. _development-attributesprocessing:

Post-processing LDAP attributes
===============================

This hook lets you post-process the attributes fetched from LDAP.

In your extension (in the :file:`ext_localconf.php` file), register the hook
using a code like:

.. code-block:: php

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ig_ldap_sso_auth']['attributesProcessing'][]
		= \VendorName\Extension\YourClass::class;

Your class has to implement the
:code:`\Causal\IgLdapSsoAuth\Utility\AttributesProcessorInterface` interface.
This implies implementing a method called :code:`processAttributes` which will
receive the following arguments:

$link
	Current LDAP link identifier, returned by ``ldap_connect()``.

$entry
	Identifier of an LDAP entry in a search result.

$attributes
	Array of LDAP attributes.

.. info::

   This hook is deprecated since version 4.1. You should migrate your code by
   listening to the PSR-14 events `AttributesProcessingEvent` instead.
