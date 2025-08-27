-----------------------------------------------------------------------------
--
--  Logical unit: F8libBookReviews
--  Component:    F8LIB
--
--  IFS Developer Studio Template Version 3.0
--
--  Date    Sign    History
--  ------  ------  ---------------------------------------------------------
-----------------------------------------------------------------------------

layer Core;

-------------------- PUBLIC DECLARATIONS ------------------------------------


-------------------- PRIVATE DECLARATIONS -----------------------------------


-------------------- LU SPECIFIC IMPLEMENTATION METHODS ---------------------
@Override
PROCEDURE Check_Insert___ (
   newrec_ IN OUT f8lib_book_reviews_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
BEGIN
   --Add pre-processing code here
   newrec_.book_reviews_id := 0;
   super(newrec_, indrec_, attr_);
   --Add post-processing code here
END Check_Insert___;

@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_book_reviews_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_book_reviews_id IS 
      SELECT MAX(book_reviews_id)
      FROM f8lib_book_reviews_tab;
   max_id_ NUMBER;
BEGIN
   --Add pre-processing code here
   OPEN get_max_book_reviews_id;
   FETCH get_max_book_reviews_id INTO max_id_;
   CLOSE get_max_book_reviews_id;
   newrec_.book_reviews_id:= NVL(max_id_,0)+1;
   Client_SYS.Add_To_Attr('BOOK_REVIEWS_ID',   newrec_.book_reviews_id,       attr_);
   Client_SYS.Add_To_Attr('COMMENT_DATE',   SYSDATE,       attr_);
   super(objid_, objversion_, newrec_, attr_);
   --Add post-processing code here
END Insert___;
-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------


PROCEDURE Add_Book_Reviews(book_id_     NUMBER,
                           user_id_     NUMBER,
                           review_comment_txt_  VARCHAR2,
                           rating_      NUMBER) 
IS
 info_       VARCHAR2(32000);
 objid_      VARCHAR2(32000);
 objversion_ VARCHAR2(32000);
 attr_       VARCHAR2(32000);
BEGIN
    Client_SYS.Clear_Attr(attr_);
    Client_SYS.Add_To_Attr('USER_ID',user_id_,attr_);
    Client_SYS.Add_To_Attr('BOOK_ID',book_id_,attr_);
    Client_SYS.Add_To_Attr('BOOK_REVIEWS_ID',0,attr_);
    Client_SYS.Add_To_Attr('REVIEW_COMMENT', review_comment_txt_, attr_);
    Client_SYS.Add_To_Attr('RATING', rating_, attr_);
   F8lib_Book_Reviews_API.New__(info_ ,
                             objid_,
                             objversion_,
                             attr_,
                             'DO');
 
 END Add_Book_Reviews;