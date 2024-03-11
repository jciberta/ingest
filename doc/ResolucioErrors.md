# InGest

## Resolució d'errors

### Internal Server Error 500

Cal mira el fitxer de log d'Apache, les últimes línies:

```
tail /var/log/apache2/error.log
```

### This function has none of DETERMINISTIC, NO SQL, or READS SQL DATA in its declaration and binary logging is enabled (you *might* want to use the less safe log_bin_trust_function_creators variable)

Cal fer:

```
mysql ...
SET GLOBAL log_bin_trust_function_creators = 1;
```

### SELECT list is not in GROUP BY clause and contains nonaggregated column ... incompatible with sql_mode=only_full_group_by

Cal fer:

```
mysql ...
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
```

## error collation utf8mb4_0900_ai_ci

cal fer replace de:
    utf8mb4_0900_ai_ci

per:
    utf8mb4_general_ci
