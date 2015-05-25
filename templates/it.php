<?php

return $template = [
    "_id" => "(//div/span/a/@href)[1]",
    "rating" => "(//div//span/@class)[1]",
    "product_title" => "(//div/b)[1]",
    "product_link" => "(//div/b/a/@href)[1]",
    "review_title" => "(//b)[1]",
    "review_author" => "(//div/a[1])[1]",
    "date" => "(//nobr)[1]",
    "verified_purchase" => "//span[@class='crVerifiedStripe']",
    "permalink" => "(//div/span/a/@href)[1]",
    "text" => "//div[@class='reviewText']",
    "main_product_link" => "(//h1/div/a/@href)[1]",
    "next" => "(//span[@class='paging']/a[contains(text(), 'Successivo ›')]/@href)[1]",
];
