# log

## Description

<details>
<summary><strong>Table Definition</strong></summary>

```sql
CREATE TABLE `log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` tinyint(3) unsigned NOT NULL,
  `event` varchar(255) NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `msg` text NOT NULL,
  `location` tinyint(4) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=[Redacted by tbls] DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
```

</details>

## Columns

| Name | Type | Default | Nullable | Extra Definition | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | ---------------- | -------- | ------- | ------- |
| id | int(11) unsigned |  | false | auto_increment |  |  |  |
| user_id | tinyint(3) unsigned |  | false |  |  |  |  |
| event | varchar(255) |  | false |  |  |  |  |
| level | tinyint(3) unsigned |  | false |  |  |  |  |
| msg | text |  | false |  |  |  |  |
| location | tinyint(4) |  | false |  |  |  |  |
| updated_at | datetime | NULL | true |  |  |  |  |
| created_at | datetime | NULL | true |  |  |  |  |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| PRIMARY | PRIMARY KEY | PRIMARY KEY (id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| PRIMARY | PRIMARY KEY (id) USING BTREE |

## Relations

![er](log.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)
