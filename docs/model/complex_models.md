# Complex models

In addition to the example model `stuff` from the model basics,
let's assume we have a secondary model `part` with the following config:
~~~json
{
  "field" : [
    "part_id",
    "part_created",
    "part_modified",
    "part_name",
    "part_stuff_id"
  ],
  "primary" : [
    "part_id"
  ],
  "foreign" : {
    "part_stuff_id" : {
      "schema": "someschema",
      "model": "stuff",
      "key" : "stuff_id"
    }
  },
  "datatype" : {
    "part_id" : "number_natural",
    "part_created" : "text_timestamp",
    "part_modified" : "text_timestamp",
    "part_name" : "text",
    "part_stuff_id" : "number_natural"
  },
  "connection" : "myconnection"
}
~~~

We can see, the structure is similar to the `stuff` model, but there is additional data in the object path `foreign.part_stuff_id`.
In SQL-terms, this would produce a foreign key constraint 'linking' `part.part_stuff_id` to `stuff.stuff_id`.
If you have your model class(es) ready, you can now do something like this:

~~~php
$model = app::getModel('stuff')
  ->addModel(app::getModel('part'));

print_r($model->search()->getResult());

// example resultset
// for clarity as a PHP array
// We assume just one entry in `stuff` and `part` respectively
$expectedExampleResultset = [
  [
    'stuff_id'        => 1,
    'stuff_created'   => '2021-11-19 12:34:56',
    'stuff_modified'  => null,
    'stuff_name'      => 'shoe',
    'part_id'         => 1,
    'part_created'    => '2021-11-19 12:34:56',
    'part_modified'    => null,
    'part_name'       => 'shoelace',
    'part_stuff_id'   => 1,
  ]
];
~~~

This effectively performs a LEFT JOIN (in SQL-terms) with both models:
~~~sql
SELECT *
FROM `stuff`
LEFT JOIN `part` ON part_id = stuff_part_id
~~~

For doing a quick-and-dirty model-building, this is absolutely sufficient, but the order of joins/models added becomes highly important for complex, ORM-supported models:

~~~php
$model = app::getModel('customer')->setVirtualFieldResult(true)
  ->addModel(app::getModel('address'))
  ->addCollectionModel(
    app::getModel('invoice')->setVirtualFieldResult(true)
      ->addCollectionModel(
        app::getModel('invoiceitem'),
        'invoice_items'
      ),
    'customer_invoices'  
  );
~~~

Given some imagination and common sense, this could yield datasets like this (_created and _modified fields left out for the sake of readability):
~~~php
[
  'customer_id'         => 234,
  'customer_firstname'  => 'John',
  'customer_lastname'   => 'Doe',
  'customer_address_id' => 8765,
  'customer_address' => [
    'address_id'      => 8765,
    'address_country' => 'DE',
    'address_zipcode' => 12345,
    'address_city'    => 'Some City',
    'address_street'  => 'Some Street',
    'address_houseno' => '5A',
  ],
  'customer_invoices' => [
    [
      'invoice_id'          => 765,
      'invoice_date'        => '2021-11-01',
      'invoice_customer_id' => 234
      'invoice_paid'        => true,
      'invoice_sum_net'     => 2000.0
      'invoice_items' => [
        [
          'invoiceitem_id'          => 88646,
          'invoiceitem_invoice_id'  => 765,
          'invoiceitem_text'        => 'Service fee',
          'invoiceitem_value_net'   => 500.0,
        ],
        [
          'invoiceitem_id'          => 88647,
          'invoiceitem_invoice_id'  => 765,
          'invoiceitem_text'        => 'A product',
          'invoiceitem_value_net'   => 1500.0,
        ]
      ]
    ]
  ]
]
~~~

We assume several models in this example:
* **customer**: a customer dataset containing the name(s) and a reference to an `address` dataset
* **address**: an address
* **invoice**: an invoice containing date, paid status and net sum, referencing the associated customer in a FKEY
* **invoiceitem**: a single item on an invoice, referencing the invoice in a FKEY

Effectively leveraging the ORM featureset of the core framework and abstracting all the 'heavy lifting' work behind the scenes:
* **customer_address** is a virtual (non-existing) field that is dynamically filled with the **address** dataset identified by the FKEY **customer_address_id**  
  (Reference: `customer.customer_address_id`->`address.address_id`)
* **customer_invoices** is a virtual (non-existing) collection field that is dynamically filled with an **array** of **invoice** datasets  
  (Reference: `invoice.invoice_customer_id`->`customer.customer_id`)
* **invoice_items** is a collection like **customer_invoices**, but nested  
  (Reference: `invoiceitem.invoiceitem_invoice_id`->`invoice.invoice_id`)
