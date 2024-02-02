### Доступные команды

- make start (Запускает контейнеры и рассылку сообщений)
- make logs (Просмотр логов обработки сообщений. Логи всех реплик слушателя)
#### Не получилось написать установку composer пакетов из под dockerfile, из-за чего после того, как сбилдятся контейнеры, нужно зайти в них и поставить пакеты. Не успел это автоматизировать


### Кастомизация

- В Makefile можно отредактировать колличество реплик слушателя, изменив переменную CONSUMERS_AMOUNT
- В команде, которая стартует рассылку сообщений [services->gateway->app->Console->Commands->TestCommand] можно отредактировать желаемое колличество сообщений и пользователей в системе

Реализовал проверку порядка доставки сообщений через PgSQL, но не считаю это решение единсвенно верным. Если уж делать гарантию порядка доставки через хранилище дополнительное, то лучше это было сделать через Redis.
В целом идея реализована так, что получатель проверяет, есть ли у пользователя более ранние неотправленные ивенты в хранилище и переотправляет в очередь этот ивент, если такие имеются.
Когда ивент обрабатывается, он удаляется из хранилища.

Пытался сначала сделать на каких-то стандартных инструментах Rabbit`a, но всё упирается в идею того, что может появляться больше одного слушателя, из-за чего Rabbit не может нам обеспечить доставку сообщений в нужном порядке.
Поэтому сделал внешний буфер, который бы обсепечивал правильную очередность.

Мне не совсем понравилась скорость обработки сообщений, но возможно это ограничение на моей машине, поскольку дилей между сообщениями в 1у секунду бывает только в начале, потом будто память забивается и задержка вплоть до 5ти секунд появляется.