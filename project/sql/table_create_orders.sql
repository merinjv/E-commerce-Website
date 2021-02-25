CREATE TABLE Orders
(
    id         int auto_increment,
    user_id    int,
    address    varchar(60) NOT NULL,
    payment_method text,
    total_price      decimal(12, 2) default 0.00,
    created     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    primary key (id),
    foreign key (user_id) references Users (id)
)

