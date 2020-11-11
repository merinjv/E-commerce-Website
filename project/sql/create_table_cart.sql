CREATE TABLE Carts
(
    id         int auto_increment,
    name       varchar(60) NOT NULL unique,
    product_id int,
    quantity   int,
    user_id    int,
    price      decimal(12, 2) default 0.00,
    primary key (id),
    foreign key (product_id) references Products (id),
    foreign key (user_id) references Users (id),
    UNIQUE KEY (product_id, user_id)
)
