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

### Full example:

	{exp:channel:entries channel="parent_weblog"}
	<p>{title}</p>
	<ul>
		<li>{nsm_entry_select_field}</li>
		<li>{nsm_entry_select_field value="title"}</li>
		<li>{nsm_entry_select_field value="field_id_33"}</li>
		<li>{nsm_entry_select_field value="field_id_33" multi_field="yes"}</li>
		<li>{nsm_entry_select_field value="field_id_33" multi_field="yes" multi_field_glue="*" }</li>
	</ul>
	{/exp:channel:entries}

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

### `glue=', '`

The glue for the returned entry values. Default glue is a pipe "|".

Example: Output the selected entry id's concatenated with a "," ie: `1,2,3`. Use this in `{exp:query}` tag `WHERE entry_id IN()` conditional like so:

	<ul>
	{exp:query sql="
	    SELECT * 
	    FROM `exp_channel_titles`
	    WHERE `entry_id`
	    IN({my_entry_relationship_field glue=","});
	"}
	    <li>{title}</li>
	{/exp:query}
	</ul>

### `multi_field='yes'`

Related entries may have custom fields such as multi-select and checkboxes that store selected values in a pipe delimited string. In some cases you may want to replace the pipe with a human readable value such as a comma.

Example:

The related entry has a multi select box with 3 possible values: apples, oranges and lemons. The field id is "20". If the related entry has apples and oranges selected the value of the custom field will be `apples|oranges`.

	Outputs: apples|oranges
	{my_entry_relationship_field value="field_id_20"}
	
	Outputs: apples, oranges
	{my_entry_relationship_field value="field_id_20" multi_field="yes"}

The example above assumes that there is only one related entry.

### `multi_field_glue=', '`

Multi-select custom fields are glued with ", " by default. The glue string can be overridden with the `multi_field_glue=''` parameter.

Example:

	Outputs: apples|oranges
	{my_entry_relationship_field value="field_id_20"}

	Outputs: apples, oranges
	{my_entry_relationship_field value="field_id_20" multi_field="yes"}

	Outputs: apples * oranges
	{my_entry_relationship_field value="field_id_20" multi_field="yes" multi_field_glue=" * "}