# 04-testing

Use Pest and factories, with focused tests for every Action including denied and invalid inputs. Preserve existing behavior unless a documented migration changes it. Tests use SQLite :memory:, array cache/session, mail array and sync queue. Exercise module installation, removal and independent operation. Run composer check-all before concluding.
