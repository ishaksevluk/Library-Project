<!DOCTYPE html>
<html>
<head>
    <title>Yorum Test</title>
    <meta charset="utf-8">
</head>
<body>
    <h1>Yorum Ekleme Test</h1>
    
    <form id="testForm">
             <div>
         <label>Book ID:</label>
         <input type="number" id="bookId" value="100" required>
     </div>
        <div>
            <label>Rating:</label>
            <select id="rating">
                <option value="5">5 Yıldız</option>
                <option value="4">4 Yıldız</option>
                <option value="3">3 Yıldız</option>
                <option value="2">2 Yıldız</option>
                <option value="1">1 Yıldız</option>
            </select>
        </div>
        <div>
            <label>Comment:</label>
            <textarea id="comment" rows="3" required>Test yorumu</textarea>
        </div>
        <button type="submit">Test Et</button>
    </form>
    
    <div id="result"></div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('book_id', document.getElementById('bookId').value);
            formData.append('rating', document.getElementById('rating').value);
            formData.append('comment', document.getElementById('comment').value);
            
            try {
                const response = await fetch('yorum_ekle.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.text();
                document.getElementById('result').innerHTML = '<pre>' + result + '</pre>';
                
                try {
                    const jsonResult = JSON.parse(result);
                    console.log('JSON Result:', jsonResult);
                } catch (e) {
                    console.log('Raw response:', result);
                }
            } catch (error) {
                document.getElementById('result').innerHTML = '<p style="color: red;">Hata: ' + error.message + '</p>';
            }
        });
    </script>
</body>
</html>