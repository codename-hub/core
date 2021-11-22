# Validator

A validator validates a given value - which might be a primitive, or even complex, nested data.
At the same time, a validator implicitly defines a 'data type' (which can be a logical type).

For example, strings are usually meant to be of type `text` - which equals the validator `codename\core\validator\text`.
If your string is a ISO-formatted datetime (f.e. `2021-11-22 12:34:56`), you can use the validator `text_timestamp` (class: `codename\core\validator\text\timestamp`), which is the default one to use for this kind of logical type (and for historical reasons).

Defining this kind of field in a model will help the **architect** to create an appropriate column in your database, as we still handle those datetime values as strings in PHP, but we leverage DB-provided, optimized fields/types/columns for this, if available.

Some basic validators are already provided in the core framework, here's an overview of some important ones and some to give an impression of logical (sub-)types.

|Validator/Data type|Class (rel. to codename\core\validator)|Description|
|---------|-------------------------------------------|-----------|
boolean|`boolean`|Bool validation
text|`text`|Text validation
text_email|`text\email`|Email validation
text_json|`text\json`|JSON string validation
text_color_hex|`text\color\hex`|Color string in HEX-format (f.e. `#FF00FF`)
text_timestamp|`text\timestamp`|ISO-datetime formatted string
text_date|`text\date`|ISO-date formatted string
number|`number`|Base number/numerical validator, includes floats/doubles/decimals
number_natural|`number\natural`|Though misleading name, integer validation (no fractions/real parts)
structure|`structure`|Arrays, associative arrays
