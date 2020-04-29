# Configuration reference

The full configuration with default values

```yaml
knp_doctrine_behaviors:
    translatable:
        translatable_fetch_mode: 'LAZY' # Can be one of 'LAZY', 'EAGER', 'EXTRA_LAZY'
        translation_fetch_mode: 'LAZY' # Can be one of 'LAZY', 'EAGER', 'EXTRA_LAZY'
    blameable:
        user_entity: null # User entity class name used to create OneToMany realations between blamable entities and users
    timestampable:
        date_field_type: 'datetime' # Can be one of 'datetime', 'datetimetz'
```    
