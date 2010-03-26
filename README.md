NSM Entry Select
================

Store a pipe delimited list of one or more selected entry ids as custom field content. Entry selections can be restricted by channels on a per field basis.

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

Let's say your custom field name is `my_entry_relationship`. In your templates you have one tag: `{my_entry_relationship}`

This tag accepts the following parameters:

### `value='id'`

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

	{my_entry_relationship value="title"} or {my_entry_relationship value="url_title"}

Custom field values can also be retrieved but for now you'll need to know the custom field id. Example:

	{my_entry_relationship value="field_id_12"}

### `divider='|'`

The glue for the returned entry values. Default glue is a pipe.

Example 1: Outputs the selected entry id's concatenated with a "|" ie: 1|2|3 Use this in embedded `{exp:channel:entries}` tag `entry_id` parameter.

	{my_entry_relationship}

Example 2: Outputs the selected entry id's concatenated with a "," ie: 1,2,3 Use this in `{exp:query}` tag `WHERE entry_id IN()` conditional.

	<ul>
	{exp:query sql="
	    SELECT * 
	    FROM `exp_channel_titles`
	    WHERE `entry_id`
	    IN({my_entry_relationship, divider=","});
	"}
	    <li>{title}</li>
	{/exp:query}
	</ul>