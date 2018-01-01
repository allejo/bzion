# Writing Database Migrations

Database migrations for BZiON are written using the [Phinx project](https://phinx.org/). With the exception of some very rare cases, database migrations should never be make use of the kernel or models; this'll lead to dependency issues. Additionally, they should never they be changed after they've been committed to the master branch with the exception of adding documentation.

## Using the `change()` Method

This method allows you write reversible migrations without having to explicitly define an `up()` or `down()` method.

View the [project website](https://book.cakephp.org/3.0/en/phinx/migrations.html#the-change-method) for more detailed information. However, these are some quick notes to avoid looking up the information every time.

The following commands can be used in this method and Phinx will automatically reverse them when rolling back:

- `createTable()`
- `renameTable()`
- `addColumn()`
- `renameColumn()`
- `addIndex()`
- `addForeignKey()`

Remember to call `create()` or `update()` and NOT `save()` when working with the Table class.

If a command cannot be reversed then Phinx will throw a `IrreversibleMigrationException` exception when it’s migrating down.
