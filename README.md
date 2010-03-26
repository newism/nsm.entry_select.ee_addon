NSM Entry Select - Simple entry relationships
=============================================

Screenshots
-----------

[![NSM Entry Select - Publish field](http://s3.amazonaws.com/ember/v5GaN2Vzxo4cADHcgiu91rW4dtN35LUZ_s.png)](http://emberapp.com/leevigraham/images/nsm-entry-select-field-settings/ "NSM Entry Select - Publish field") [![NSM Entry Select - Field settings](http://s3.amazonaws.com/ember/AAZWSU8ATPOwHWGIlqVOSKyC3bSad5rp_s.png)](http://emberapp.com/leevigraham/images/nsm-entry-select-publish-field/ "NSM Entry Select - Field settings")

Installation
------------

1. Download
2. Rename folder to `nsm_entry_select`
3. Copy to your `/system/expressionengine/thrid_party/` directory
4. Install fieldtype
5. Create a new field.

Usage
-----

Let's say your custom field name is `my_entry_relationship_field`. In your templates you have one tag: `{my_entry_relationship_field}`

By default this tag returns a pipe delimited string of the related entry ids - great to use as tag parameters.

### Example: Rendering related entries using the `{exp:channel:entries}` tag and a collection embed template.

This technique requires a parent templates and a "collection" embed.

The parent template: `site/index`:

	{!-- The entry with NSM Entry Select custom field value --}
	{exp:channel:entries weblog="my_weblog" limit="1"}
		<h1>Title</h1>
		{embed="site/related_entries" related_ids="{my_entry_relationship_field}"}
	{/exp:channel:entries}

The collection embed template: `site/related_entries`:

	{!--
		Pass the related entry ids into a second weblog entries tag
		The passed custom field value will look something like: 1|2|3
		The tag below *will* render the related entries
	--}
	<h2>Related Entries</h2>
	<ul>
		{exp:channel:entries weblog="my_related_weblog" entry_id="{embed:related_ids}"}
		<li>{title}</li>
		{/exp:channel:entries}
	</ul>
	
	{!--
		You could also show all but the related entries
		The tag below *will not* render the related entries
	--}
	<h2>Un-related Entries</h2>
	<ul>
		{exp:channel:entries weblog="my_related_weblog" entry_id="not {embed:related_ids}"}
		<li>{title}</li>
		{/exp:channel:entries}
	</ul>

Parameters
----------

This tag accepts the following parameters:

### `value='entry_id'`

The entry value to return. By default this is the entry id but it can be any of the standard entry attributes:

* `entry_id`
* `site_id`
* `channel_id`
* `author_id`
* `forum_topic_id`
* `title`
* `url_title`
* `status`
* `view_count_one`
* `view_count_two`
* `view_count_three`
* `view_count_four`
* `allow_comments`
* `sticky`
* `comment_total`

Example:

	{my_entry_relationship_field value="title"} or {my_entry_relationship_field value="url_title"}

Custom field values can also be retrieved but for now you'll need to know the custom field id. Example:

	{my_entry_relationship_field value="field_id_12"}

Note: Pulling custom data will require one or more small DB calls.

### `divider='|'`

The glue for the returned entry values. Default glue is a pipe.

Example 1: Outputs the selected entry id's concatenated with a "|" ie: 1|2|3 Use this in embedded `{exp:channel:entries}` tag `entry_id` parameter.

	{my_entry_relationship_field}

Example 2: Outputs the selected entry id's concatenated with a "," ie: 1,2,3 Use this in `{exp:query}` tag `WHERE entry_id IN()` conditional.

	<ul>
	{exp:query sql="
	    SELECT * 
	    FROM `exp_channel_titles`
	    WHERE `entry_id`
	    IN({my_entry_relationship_field, divider=","});
	"}
	    <li>{title}</li>
	{/exp:query}
	</ul>