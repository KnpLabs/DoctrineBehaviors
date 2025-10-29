# Translatable - Keep existing content with Doctrine Migration

When adding Translatable to an existing project, Doctrine Migration is going to blindly remove your table columns as you moved them to Translatable dedicated entities.

Here is how you can edit your Doctrine Migration to keep your contributed database content.

Let's see an example with a **Product** entity owning a **description** property. You moved this property to the **ProductTranslation** entity. Your initial Doctrine migration will look like this:

```php
final class Version20221111111111 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_BE61F5EA2C2AC5D3 (translatable_id), UNIQUE INDEX product_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_translation ADD CONSTRAINT FK_BE61F5EA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP description');
    }
    
    // ...
}
```

The last SQL is problematic: `DROP description` will permanently delete all the contributed descriptions.

All you need to do is add a new SQL statement manually, between the **CREATE TABLE** and the **DROP description**, to move your content using the **INSERT ... SELECT** notation:

```sql
INSERT INTO product_translation (translatable_id, description, locale) SELECT id, description, 'en' FROM product;
```

The modification is as follows:

```diff
public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE product_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_BE61F5EA2C2AC5D3 (translatable_id), UNIQUE INDEX product_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE product_translation ADD CONSTRAINT FK_BE61F5EA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product (id) ON DELETE CASCADE');
+    $this->addSql('INSERT INTO product_translation (translatable_id, description, locale) SELECT id, description, 'en' FROM product;');
    $this->addSql('ALTER TABLE product DROP description');
}
```

Make sure to add all your fields and edit the locale if you don't come from "en" like in the example.
