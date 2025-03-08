

### Summarize the database schema:

1. users.sql:
- Complete user management system
- Password history tracking with trigger
- OAuth integration
- Role-based access control
- Session management
- Security features (password reset, lockout)

2. papers.sql:
- UUID-based primary key (CHAR(36))
- Multiple author support
- Comprehensive paper workflow states
- Review tracking
- Payment status integration

3. reviews.sql:
- Detailed review scoring system
- Multiple review criteria
- Unique constraint on paper-reviewer combination
- Status tracking
- Deadline management

4. journal_details.sql:
- Journal metadata
- Call for Papers (CFP) management
- Issue tracking
- JSON support for CFP topics

5. activity_logs.sql:
- User activity tracking
- JSON field for flexible logging
- IP and user agent tracking
- Soft delete support

6. payments.sql:
- Transaction tracking
- Multiple payment methods
- Invoice management
- Status tracking


## The relationships are properly set up:

users ← papers (author_id, co_authors, reviewer_id)
users ← reviews (reviewer_id)
papers ← reviews (paper_id)
users ← activity_logs (user_id)
papers ← payments (paper_id)
users ← payments (user_id)

## All tables use:
- InnoDB engine
- UTF8MB4 character set
- Proper indexing
- Appropriate foreign key constraints
- Timestamp tracking
- Would you like me to provide any specific 
- queries or code for working with these tables?