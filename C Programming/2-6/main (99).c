#include<stdio.h>
#include<conio.h>
void main()
{
   
    int marks[3][3]={{1,2,3},{4,5,6},{7,8,9}};
    int i,j;
    
    for(int i=0;i<3;i++)
    {
       for(int j=0;j<3;j++)
       {
           printf("[%d] [%d] =  %d ",i,j,marks[i][j]);
       }
       printf("\n");
    }
    
    
    
        
    getch();
}