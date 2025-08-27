-----------------------------------------------------------------------------
--
--  Logical unit: F8libBooks
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
PROCEDURE Do_Borrow___ (
   rec_  IN OUT NOCOPY f8lib_books_tab%ROWTYPE,
   attr_ IN OUT NOCOPY VARCHAR2 )
IS
   
BEGIN
   NULL;
END Do_Borrow___;


PROCEDURE Do_Delete___ (
   rec_  IN OUT NOCOPY f8lib_books_tab%ROWTYPE,
   attr_ IN OUT NOCOPY VARCHAR2 )
IS
   
BEGIN
   NULL;
END Do_Delete___;


PROCEDURE Do_Return___ (
   rec_  IN OUT NOCOPY f8lib_books_tab%ROWTYPE,
   attr_ IN OUT NOCOPY VARCHAR2 )
IS
   v_reservation_id f8lib_reservations_tab.reservation_id%TYPE;
BEGIN

   SELECT reservation_id
   INTO v_reservation_id
   FROM (
      SELECT reservation_id
      FROM f8lib_reservations_tab
      WHERE book_name = rec_.book_name
        AND author_id = rec_.author_id
        AND rowstate = 'Reserved'
        AND notification_date IS NULL
      ORDER BY request_date
   )
   WHERE ROWNUM = 1;


   UPDATE f8lib_reservations_tab
   SET notification_date = SYSDATE
   WHERE reservation_id = v_reservation_id;

   UPDATE f8lib_borrow_records_tab
   SET return_date = SYSDATE
   WHERE book_id = rec_.book_id
     AND return_date IS NULL;

EXCEPTION
   WHEN NO_DATA_FOUND THEN
   
      UPDATE f8lib_borrow_records_tab
      SET return_date = SYSDATE
      WHERE book_id = rec_.book_id
        AND return_date IS NULL;

   WHEN OTHERS THEN
      Error_SYS.Record_General('F8libBooks', 'DO_RETURN_FAILED', SQLERRM, NULL);
END Do_Return___;

@Override
PROCEDURE Check_Insert___ (
   newrec_ IN OUT f8lib_books_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
BEGIN
   --(+)20250725 ishak (basla)
   newrec_.book_id := 0;   
   super(newrec_, indrec_, attr_);
  --(+)20250725 ishak (bitti)
END Check_Insert___;



@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_books_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_book_id IS 
      SELECT MAX(book_id)
      FROM f8lib_books_tab;
   max_id_ NUMBER;
BEGIN
    OPEN get_max_book_id;
   FETCH get_max_book_id INTO max_id_;
   CLOSE get_max_book_id;
   newrec_.book_id:= NVL(max_id_,0)+1;
   Client_SYS.Add_To_Attr('BOOK_ID',   newrec_.book_id,       attr_);
   Client_SYS.Add_To_Attr('ADDED_DATE',   SYSDATE,       attr_);
    --(+)20250725 ishak (basla)
   super(objid_, objversion_, newrec_, attr_);
    --(+)20250725 ishak (bitti)
END Insert___;

@Override
PROCEDURE Check_Delete___ (
   remrec_ IN f8lib_books_tab%ROWTYPE )
IS
BEGIN
   --Add pre-processing code here
   super(remrec_);
   --Add post-processing code here
END Check_Delete___;



-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------
