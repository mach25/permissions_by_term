Permissions by Term
====================================

DESCRIPTION
-----------
Restricts users from the view of the taxonomy term page, if you've replaced
the listing of nodes, which are attached to a taxonomy term, by the view. To
text this, replace drupal's default listing-page of nodes, which are attached
to a taxonomy term (e.g. taxonomy/term/TERM-ID) by a view (project url of the
widely used views-mdoule: http://drupal.org/project/views).

Permissions by Term module also disallows users to select taxonomy terms, for
which they don't have access.

Also user's, which are not allowed for a specific taxonomy term, aren't allowed
to view the attached node.

WHY THIS MODULE WAS CREATED AND HOW?
------------------------------------
During work on a client project the Taxonomy Term Permissions module was
forked. It couldn't handle a different language and couldn't handle permissions
on a views page with listed taxonomy terms.
