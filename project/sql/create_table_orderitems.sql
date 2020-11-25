CREATE TABLE OrderItems
(
    id         int auto_increment,
    order_id    int,
    product_id    int,
    quantity    int,
    unit_price      decimal(12, 2) default 0.00,
    created     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    primary key (id),
    foreign key (product_id) references Products (id),
    foreign key (order_id) references Orders (id),
    foreign key (user_id) references Users (id),
    UNIQUE KEY (product_id, order_id, user_id)
)