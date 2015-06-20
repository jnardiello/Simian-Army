<?php

return $template = [
    "context" => "(//div[contains(concat(' ', @class, ' '), ' review ')])[1]",
    "_id" => "(//div[contains(concat(' ', @class, ' '), ' review ')]/@id)[1]",
    "rating" => "(//i/span)[1]",
    "product_title" => "(//div[contains(concat(' ', @class, ' '), ' product-title ')]//a)[1]",
    "product_link" => "(//div[contains(concat(' ', @class, ' '), ' product-title ')]//a/@href)[1]",
    "review_title" => "(//a[contains(concat(' ', @class, ' '), ' review-title ')])[1]",
    "review_author" => "(//div[contains(concat(' ', @class, ' '), ' review ')]//div[@class='a-row']//a[contains(concat(' ', @class, ' '), ' author ')])[1]",
    "date" => "(//span[contains(concat(' ', @class, ' '), ' review-date ')])[1]",
    "verified_purchase" => "(//span[@class='a-declarative']/a/span)[1]",
    "permalink" => "(//a[contains(concat(' ', @class, ' '), ' review-title')]/@href)[1]",
    "text" => "//span[contains(concat(' ', @class, ' '), ' review-text ')]",
    "main_product_link" => "(//div[contains(concat(' ', @class, ' '), ' product-title ')]//a/@href)[1]",
    "next" => "(//ul[@class='a-pagination']//li[@class='a-last']/a/@href)[1]",
];
