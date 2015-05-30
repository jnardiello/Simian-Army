<?php

return $template = [
    "_id" => "(//div[@id='cm_cr-review_list']/div/@id)[1]",
    "rating" => "(//i/span)[1]",
    "product_title" => "(//a[contains(concat(' ', @class, ' '), ' a-size-mini ')])[1]",
    "product_link" => "(//a[contains(concat(' ', @class, ' '), ' a-size-mini ')]/@href)[1]",
    "review_title" => "(//a[contains(concat(' ', @class, ' '), ' review-title ')])[1]",
    "review_author" => "(//div[contains(concat(' ', @class, ' '), ' review ')]//div[@class='a-row']//a[contains(concat(' ', @class, ' '), ' author ')])[1]",
    "date" => "(//div[contains(concat(' ', @class, ' '), ' review ')]//span[contains(concat(' ', @class, ' '), ' review-date ')])[1]",
    "verified_purchase" => "(//span[@class='a-declarative']/a/span)[1]",
    "permalink" => "(//div/span/a/@href)[1]",
    "text" => "//span[contains(concat(' ', @class, ' '), ' review-text ')]",
    "main_product_link" => "(//div[contains(concat(' ', @class, ' '), ' product-title ')]//a/@href)[1]",
    "next" => "(//ul[@class='a-pagination']//li[@class='a-last']/a/@href)[1]",
];
